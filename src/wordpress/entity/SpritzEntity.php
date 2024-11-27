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

namespace dev\wisdomtree\spritz\wordpress\entity;

use dev\wisdomtree\spritz\api\entity\ISpritzEntity;
use dev\wisdomtree\spritz\api\entity\SpritzState;

class SpritzEntity extends BaseEntity implements ISpritzEntity {
	protected $uid;
	protected $entity_id;
	protected $entity_type;
	protected $current_state;
	protected $action_date;
	protected $next_review_date;
	protected $public_note;
	protected $private_note;

	private const FIELDS = [
		'id' => [ 't' => 'int', 'd' => 0 ],
		'uid' => [ 't' => 'int', 'd' => 0 ],
		'entity_id' => [ 't' => 'int', 'd' => 0 ],
		'entity_type' => [ 't' => 'string', 'd' => 'post' ],
		'current_state' => [ 't' => 'spritzstate', 'd' => SpritzState::NEUTRAL ],
		'action_date' => [ 't' => 'int', 'd' => 0 ],
		'next_review_date' => [ 't' => 'int', 'd' => 0 ],
		'public_note' => [ 't' => 'string', 'd' => '' ],
		'private_note' => [ 't' => 'string', 'd' => '' ],
	];

	public function __construct( array $data = [] ) {
		parent::__construct( $data );
	}

	public function getId() : int { return $this->id; }
	public function getUid() : int { return $this->uid; }
	public function getEntityId() : int { return $this->entity_id; }
	public function getEntityType() : string { return $this->entity_type; }
	public function getCurrentState() : SpritzState { return $this->current_state; }
	public function getActionDate() : int { return $this->action_date; }
	public function getNextReviewDate() : int { return $this->next_review_date; }
	public function getPublicNote() : string { return $this->public_note; }
	public function getPrivateNote() : string { return $this->private_note; }

	public function setCurrentState( SpritzState $val ) : void { $this->current_state = $val; }

	public function toArray() : array {
		$ary = parent::toArray();

		if ( !empty( $ary[ 'current_state' ] ) && $ary[ 'current_state' ] instanceof SpritzState ) {
			$ary[ 'current_state' ] = $ary[ 'current_state' ]->name;
		}

		return $ary;
	}

	protected function getFields() : array {
		return self::FIELDS;
	}
}
