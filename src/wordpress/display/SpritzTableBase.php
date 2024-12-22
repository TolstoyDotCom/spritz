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

namespace dev\wisdomtree\spritz\wordpress\display;

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

use dev\wisdomtree\spritz\api\installation\IDirectories;
use dev\wisdomtree\spritz\api\controller\ISpritzController;
use dev\wisdomtree\spritz\api\controller\ISettingsController;
use dev\wisdomtree\spritz\api\utils\IUtils;
use WP_List_Table;

class SpritzTableBase extends WP_List_Table {
	private const SORT_DATA_POSSIBLE_INPUTS = [
		'orderby',
		'order',
		'page',
		'entity_id',
	];

	public function __construct( protected readonly IDirectories $directories,
									protected readonly ISpritzController $spritzController,
									protected readonly ISettingsController $settingsController,
									protected readonly IUtils $utils ) {
		parent::__construct();
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare_items() {
		$total = $this->spritzController->countLatest();
		$count = $this->get_items_per_page( 'elements_per_page', 10 );
		$startingRow = $count * ( $this->get_pagenum() - 1 );
		$requestData = $this->getRequestData();
		$temp = $this->spritzController->loadLatestMultiple( $count, $startingRow, $requestData[ 'column' ], $requestData[ 'order' ] );
		$this->items = [];
		foreach ( $temp as $item ) {
			$this->items[] = $item->toArray();
		}

		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
			'current_state',
		];

		$this->set_pagination_args([
			'total_items' => $total,
			'per_page'    => $count,
			'total_pages' => ceil( $total / $count ),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'uid':
				$uid = (int) $item[ $column_name ];
				$url = $uid ? get_author_posts_url( $uid ) : '';

				return $url ? '<a href="' . esc_url( $url ) . '">' . $uid . '</a>' : '';

			case 'entity_id':
				$entity_id = (int) $item[ $column_name ];
				$url = $entity_id ? get_permalink( $entity_id ) : '';

				return $url ? '<a href="' . esc_url( $url ) . '">' . $entity_id . '</a>' : '';

			case 'action_date':
				return date( 'M j, Y, g:i a e', $item[ $column_name ] );

			case 'next_review_date':
				return date( 'M j, Y, g:i a e', $item[ $column_name ] );

			case 'id':
				return (int) $item[ $column_name ];

			case 'history':
				$id = (int) $item[ 'entity_id' ];
				if ( !$id ) {
					return '';
				}

				$url = esc_url( admin_url() . 'admin.php?page=spritz-history&entity_id=' . $id );

				return '<a href="' . $url . '">link</a>';

			case 'entity_type':
			case 'current_state':
			case 'public_note':
			case 'private_note':

			default:
				return esc_html( $item[ $column_name ] );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_sortable_columns() {
		return [
			'id' => [
				'id',
				FALSE,
			],
			'uid' => [
				'uid',
				FALSE,
			],
			'entity_id' => [
				'entity_id',
				FALSE,
			],
			'entity_type' => [
				'entity_type',
				FALSE,
			],
			'current_state' => [
				'current_state',
				FALSE,
			],
			'action_date' => [
				'action_date',
				FALSE,
			],
			'next_review_date' => [
				'next_review_date',
				FALSE,
			],
		];
	}

	protected function getRequestData() {
		$input = $this->utils->stripSlashesScalarKeys( $_REQUEST, self::SORT_DATA_POSSIBLE_INPUTS );

		$ret = [
			'column' => 'id',
			'order' => 'ASC',
			'page' => 0,
			'entity_id' => 0,
		];

		if ( !empty( $input[ 'orderby' ] ) ) {
			$temp = $input[ 'orderby' ];
			$cols = $this->get_columns();
			unset( $cols[ 'cb' ] );
			if ( !empty( $cols[ $temp ] ) ) {
				$ret[ 'column' ] = $temp;
			}
		}

		if ( !empty( $input[ 'order' ] ) ) {
			$temp = strtoupper( $input[ 'order' ] );
			if ( in_array( $temp, [ 'ASC', 'DESC' ], TRUE ) ) {
				$ret[ 'order' ] = $temp;
			}
		}

		if ( !empty( $input[ 'page' ] ) && ctype_digit( '' . $input[ 'page' ] ) ) {
			$temp = (int) $input[ 'page' ];
			if ( $temp < 1 || $temp > 10000 ) {
				$temp = 0;
			}

			$ret[ 'page' ] = $temp;
		}

		if ( !empty( $input[ 'entity_id' ] ) && ctype_digit( '' . $input[ 'entity_id' ] ) ) {
			$temp = (int) $input[ 'entity_id' ];
			if ( $temp < 1 || $temp > 1000000000 ) {
				$temp = 0;
			}

			$ret[ 'entity_id' ] = $temp;
		}

		return $ret;
	}

}
