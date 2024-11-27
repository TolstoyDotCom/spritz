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

use dev\wisdomtree\spritz\api\entity\SpritzState;

abstract class BaseEntity {
	protected $id;

	public function __construct( array $data = [] ) {
		foreach ( $this->getFields() as $fieldName => $descriptor ) {
			if ( !isset( $data[ $fieldName ] ) ) {
				$this->$fieldName = $descriptor[ 'd' ];
			}
			else {
				$this->$fieldName = $this->coerceValue( $data[ $fieldName ], $descriptor[ 't' ] );
			}
		}
	}

	public function getId() : int { return $this->id; }

	abstract protected function getFields() : array;

	public function toArray() : array {
		$ary = [];

		foreach ( $this->getFields() as $fieldName => $descriptor ) {
			$ary[ $fieldName ] = $this->$fieldName;
		}

		return $ary;
	}

	public function __toString() : string {
		$ary = $this->toArray();
		$ret = [];

		foreach ( $ary as $fieldName => $val ) {
			$ret[] = $fieldName . '=' . $val;
		}

		return implode( ', ', $ret );
	}

	protected function coerceValue( $val, $type ) {
		if ( $type === 'string' ) {
			return (string) $val;
		}
		else if ( $type === 'int' ) {
			return (int) $val;
		}
		else if ( $type === 'bool' ) {
			return (bool) $val;
		}
		else if ( $type === 'spritzstate' ) {
			if ( $val instanceof SpritzState ) {
				return $val;
			}

			$val = SpritzState::tryFrom( $val );
			if ( !$val ) {
				$val = SpritzState::NEUTRAL;
			}

			return $val;
		}

		return $val;
	}
}
