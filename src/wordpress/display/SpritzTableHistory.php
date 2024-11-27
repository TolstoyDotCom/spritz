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

use dev\wisdomtree\spritz\api\installation\IDirectories;
use dev\wisdomtree\spritz\api\controller\ISpritzController;
use dev\wisdomtree\spritz\api\controller\ISettingsController;

class SpritzTableHistory extends SpritzTableBase {
	public function __construct( IDirectories $directories,
									ISpritzController $spritzController,
									ISettingsController $settingsController ) {
		parent::__construct( $directories, $spritzController, $settingsController );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_columns() {
		return [
			//'cb' => '<input type="checkbox" class="spritz_listing_selectall" />',
			'id' => __('ID', 'spritz'),
			'uid' => __('User', 'spritz'),
			'entity_id' => __('Entity ID', 'spritz'),
			'entity_type' => __('Entity type', 'spritz'),
			'current_state' => __('Current state', 'spritz'),
			'action_date' => __('Action date', 'spritz'),
			'next_review_date' => __('Next review date', 'spritz'),
			'public_note' => __('Public note', 'spritz'),
			'private_note' => __('Private note', 'spritz'),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare_items() {
		$requestData = $this->getRequestData();
		if ( empty( $requestData[ 'entity_id' ] ) ) {
			exit;
		}

		$temp = $this->spritzController->loadHistory( $requestData[ 'entity_id' ] );
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
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_sortable_columns() {
		return [];
	}
}
