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

use dev\wisdomtree\spritz\api\entity\ISettingsEntity;

interface ISettingsController {
	public function save( ISettingsEntity $entity ) : bool;

	public function load( int $id ) : ISettingsEntity;

	public function registerHooks() : void;

	public function addMenu() : void;

	public function settingsPage() : void;

	public function registerSettingsPage() : void;

	public function settingsSectionCallback() : void;

	public function settingsFieldApprovalsCallback() : void;

	public function settingsFieldDaysBeforeCallback() : void;

	public function settingsFieldEmailAddressCallback() : void;

	public function settingsFieldEmailSubjectCallback() : void;

	public function settingsFieldEmailBodyCallback() : void;
}
