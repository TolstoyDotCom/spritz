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

namespace dev\wisdomtree\spritz\api\entity;

interface ISpritzEntity {
	public function getId() : int;
	public function getUid() : int;
	public function getEntityId() : int;
	public function getEntityType() : string;
	public function getCurrentState() : SpritzState;
	public function getActionDate() : int;
	public function getNextReviewDate() : int;
	public function getPublicNote() : string;
	public function getPrivateNote() : string;

	public function setCurrentState( SpritzState $val ) : void;

	public function toArray() : array;
}
