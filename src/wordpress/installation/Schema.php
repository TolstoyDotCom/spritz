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

namespace dev\wisdomtree\spritz\wordpress\installation;

use dev\wisdomtree\spritz\api\installation\ISchema;

class Schema implements ISchema {
	public function __construct(
		private readonly string $dbPrefix,
		private readonly string $dbCollation
	) {}

	public function getCreateCommands() : array {
		return [
			"CREATE TABLE {$this->dbPrefix}spritz_entity (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				uid bigint(20) NOT NULL,
				entity_id bigint(20) NOT NULL,
				entity_type varchar(255) NOT NULL,
				current_state varchar(255) NOT NULL,
				action_date bigint(20) NOT NULL,
				next_review_date bigint(20) NOT NULL,
				public_note text NOT NULL,
				private_note text NOT NULL,
				PRIMARY KEY (id)
			) {$this->dbCollation}",
		];
	}

	public function getDeleteCommands() : array {
		return [
			"DROP TABLE IF EXISTS {$this->dbPrefix}spritz_entity",
		];
	}
}
