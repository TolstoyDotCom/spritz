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

namespace dev\wisdomtree\spritz\api\controller;

use dev\wisdomtree\spritz\api\entity\ISpritzEntity;

interface ISpritzController {
	public function registerHooks() : void;

	public function createSpritzEntity( $uid, $entityId, $entityType, $currentState, $actionDate, $nextReviewDate, $publicNote, $privateNote ) : ISpritzEntity;

	public function processExpiredReviews( $count = 10 ) : int;

	public function save( ISpritzEntity $entity ) : int;

	public function countLatest() : int;

	public function loadLatestMultiple( int $count = 10, int $startingRow = 0, string $sortColumn = 'id', string $sortDirection = 'asc' ) : array;

	public function loadHistory( int $entityId ) : array;

	public function loadById( int $id, $createIfNecessary = TRUE ) : ?ISpritzEntity;

	public function loadLatestByEntityId( int $entityId, $createIfNecessary = TRUE ) : ?ISpritzEntity;
}
