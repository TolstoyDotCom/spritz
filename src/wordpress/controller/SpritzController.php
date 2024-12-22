<?php

/*
 * Copyright 2024 Chris Kelly
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace dev\wisdomtree\spritz\wordpress\controller;

use dev\wisdomtree\spritz\api\entity\ISpritzEntity;
use dev\wisdomtree\spritz\api\entity\SpritzState;
use dev\wisdomtree\spritz\api\controller\ISpritzController;
use dev\wisdomtree\spritz\wordpress\entity\SpritzEntity;

class SpritzController implements ISpritzController {
	private const SPRITZ_TABLE_NAME = 'spritz_entity';

	private const FIELDS = [
		'id',
		'uid',
		'entity_id',
		'current_state',
		'action_date',
		'next_review_date',
		'public_note',
		'private_note',
	];

	private const SQL_EXPIRED_REVIEWS =
		'WITH expre AS (
		SELECT e.*, ROW_NUMBER() OVER (PARTITION BY entity_id ORDER BY id DESC) AS rownum
		FROM %i AS e
		)
		SELECT * FROM expre WHERE rownum = 1 AND next_review_date < %d AND ( current_state = %s OR current_state = %s ) LIMIT %d';

	private const SQL_SAVE_ENTITY =
		'INSERT INTO %i ( id, uid, entity_id, entity_type, current_state, action_date, next_review_date, public_note, private_note ) ' .
		'VALUES( %d, %d, %d, %s, %s, %d, %d, %s, %s ) ' .
		'ON DUPLICATE KEY UPDATE ' .
		'uid = %d, ' .
		'entity_id = %d, ' .
		'entity_type = %s, ' .
		'current_state = %s, ' .
		'action_date = %d, ' .
		'next_review_date = %d, ' .
		'public_note = %s, ' .
		'private_note = %s';

	private const SQL_COUNT_LATEST =
		'WITH expre AS (
		  SELECT e.*, ROW_NUMBER() OVER (PARTITION BY entity_id ORDER BY id DESC) AS rownum
		  FROM %i AS e
		)
		SELECT COUNT(*) AS ct FROM expre WHERE rownum = 1';

	private const SQL_LOAD_HISTORY =
		'SELECT * FROM %i WHERE entity_id = %d ORDER BY id DESC';

	public function __construct(
		private readonly object $dbConnection,
		private readonly string $dbPrefix,
		private readonly string $dbCollation
	) {}

	public function registerHooks() : void {
	}

	public function createSpritzEntity( $uid, $entityId, $entityType, $currentState, $actionDate, $nextReviewDate, $publicNote, $privateNote ) : ISpritzEntity {
		return new SpritzEntity( [
			'uid' => $uid,
			'entity_id' => $entityId,
			'entity_type' => $entityType,
			'current_state' => $currentState,
			'action_date' => $actionDate,
			'next_review_date' => $nextReviewDate,
			'public_note' => $publicNote,
			'private_note' => $privateNote,
		] );
	}

	public function processExpiredReviews( $count = 10 ) : int {
		$sql = $this->dbConnection->prepare(
			self::SQL_EXPIRED_REVIEWS,
			$this->dbPrefix . self::SPRITZ_TABLE_NAME,
			time(),
			SpritzState::REVIEWED->name,
			SpritzState::APPROVED->name,
			$count
		);

		$ary = $this->dbConnection->get_results( $sql, ARRAY_A );
		if ( !$ary || !is_array( $ary ) || count( $ary ) < 1 ) {
			return 0;
		}

		$count = 0;

		foreach ( $ary as $item ) {
			try {
				$spritzEntity = $this->createSpritzEntity(
					$item[ 'uid' ],
					$item[ 'entity_id' ],
					$item[ 'entity_type' ],
					SpritzState::AWAITING_REVIEW->name,
					time(),
					time(),
					__('Automatically processed', 'spritz'),
					''
				);

				$this->save( $spritzEntity );

				$count++;
			}
			catch ( Exception $e ) {
				rawdebug( 'EXCEPTION in ' . __METHOD__ . '=' . $e->getMessage() . ' from ' . wp_json_encode( $item ) );
			}
		}

		return $count;
	}

	public function save( ISpritzEntity $entity ) : int {
		do_action( 'spritz_presave_spritz_entity', $entity );

		$publicNote = sanitize_text_field( $entity->getPublicNote() );
		$privateNote = sanitize_text_field( $entity->getPrivateNote() );

		$res = $this->dbConnection->replace(
			$this->dbPrefix . self::SPRITZ_TABLE_NAME,
			[
				'id' => $entity->getId(),
				'uid' => $entity->getUid(),
				'entity_id' => $entity->getEntityId(),
				'entity_type' => $entity->getEntityType(),
				'current_state' => $entity->getCurrentState()->name,
				'action_date' => $entity->getActionDate(),
				'next_review_date' => $entity->getNextReviewDate(),
				'public_note' => $entity->getPublicNote(),
				'private_note' => $entity->getPrivateNote(),
			],
			[
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
			]
		);

		return !empty($this->dbConnection->insert_id) ? (int) $this->dbConnection->insert_id : 0;
	}

	public function countLatest() : int {
		$sql = $this->dbConnection->prepare(
			self::SQL_COUNT_LATEST,
			$this->dbPrefix . self::SPRITZ_TABLE_NAME
		);

		$ary = $this->dbConnection->get_results( $sql, ARRAY_A );
		if ( !$ary || empty( $ary[ 0 ][ 'ct' ] ) ) {
			return 0;
		}

		return $ary[ 0 ][ 'ct' ];
	}

	public function loadLatestMultiple( int $count = 10, int $startingRow = 0, string $sortColumn = 'id', string $sortDirection = 'asc' ) : array {
		$count = $this->boxNumber( $count, 1, 1000 );
		$startingRow = $this->boxNumber( $startingRow, 0, 100000 );

		$sortColumn = strtolower( $sortColumn );
		if ( !in_array( $sortColumn, self::FIELDS, TRUE ) ) {
			$sortColumn = 'id';
		}

		$sortDirection = strtoupper( $sortDirection );
		if ( !in_array( $sortDirection, [ 'ASC', 'DESC' ], TRUE ) ) {
			$sortDirection = 'ASC';
		}

		$sql = $this->dbConnection->prepare(
'WITH expre AS (
  SELECT e.*, ROW_NUMBER() OVER (PARTITION BY entity_id ORDER BY id DESC) AS rownum
  FROM %i AS e
)
SELECT * FROM expre WHERE rownum = 1 ORDER BY ' . $sortColumn . ' ' . $sortDirection . ' LIMIT %d OFFSET %d',
			$this->dbPrefix . self::SPRITZ_TABLE_NAME,
			$count,
			$startingRow
		);

		$ary = $this->dbConnection->get_results( $sql, ARRAY_A );
		if ( !$ary ) {
			return [];
		}

		$ret = [];
		foreach ( $ary as $inner ) {
			$ret[] = new SpritzEntity( (array) $inner );
		}

		return $ret;
	}

	public function loadHistory( int $entityId ) : array {
		$sql = $this->dbConnection->prepare(
			self::SQL_LOAD_HISTORY,
			$this->dbPrefix . self::SPRITZ_TABLE_NAME,
			$entityId
		);

		$ary = $this->dbConnection->get_results( $sql, ARRAY_A );
		if ( !$ary ) {
			return [];
		}

		$ret = [];
		foreach ( $ary as $inner ) {
			$ret[] = new SpritzEntity( (array) $inner );
		}

		return $ret;
	}

	public function loadById( int $id, $createIfNecessary = TRUE ) : ?ISpritzEntity {
		$sql = $this->dbConnection->prepare(
			'SELECT * FROM %i WHERE id=%d',
			$this->dbPrefix . self::SPRITZ_TABLE_NAME,
			$id
		);

		$obj = $this->dbConnection->get_results( $sql );
		if ( empty( $obj ) ) {
			return $createIfNecessary ? new SpritzEntity() : NULL;
		}

		$obj = reset( $obj );
		if ( empty( $obj ) ) {
			return $createIfNecessary ? new SpritzEntity() : NULL;
		}

		return new SpritzEntity( (array) $obj );
	}

	public function loadLatestByEntityId( int $entityId, $createIfNecessary = TRUE ) : ?ISpritzEntity {
		$sql = $this->dbConnection->prepare(
			'SELECT * FROM %i WHERE entity_id=%d ORDER BY id DESC LIMIT 1',
			$this->dbPrefix . self::SPRITZ_TABLE_NAME,
			$entityId
		);

		$obj = $this->dbConnection->get_results( $sql );
		if ( empty( $obj ) ) {
			return $createIfNecessary ? new SpritzEntity() : NULL;
		}

		$obj = reset( $obj );
		if ( empty( $obj ) ) {
			return $createIfNecessary ? new SpritzEntity() : NULL;
		}

		return new SpritzEntity( (array) $obj );
	}

	protected function boxNumber( $input, $min, $max ) {
		$input = (int) $input;

		if ( $input < $min ) {
			return $min;
		}

		if ( $input > $max ) {
			return $max;
		}

		return $input;
	}
}
