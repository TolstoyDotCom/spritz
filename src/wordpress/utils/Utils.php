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

namespace dev\wisdomtree\spritz\wordpress\utils;

use dev\wisdomtree\spritz\api\utils\IUtils;

class Utils implements IUtils {
	public function __construct() {}

	public function stripSlashesScalarKeys( $ary, array $expectedKeys = [], $default = NULL ) {
		if ( !$ary ) {
			return $default;
		}

		if ( is_scalar( $ary ) ) {
			return is_string( $ary ) ? stripslashes( $ary ) : $ary;
		}

		if ( !is_countable( $ary ) ) {
			return $default;
		}

		$ret = [];
		foreach ( $expectedKeys as $expectedKey ) {
			if ( !isset( $ary[ $expectedKey ] ) ) {
				continue;
			}

			if ( !is_scalar( $ary[ $expectedKey ] ) ) {
				$ret[ $expectedKey ] = NULL;
			}

			$ret[ $expectedKey ] = is_string( $ary[ $expectedKey ] ) ? stripslashes( $ary[ $expectedKey ] ) : $ary[ $expectedKey ];
		}

		return $ret;
	}

	public function stripSlashesScalarLimit( $ary, int $limit = 20, $default = NULL ) {
		if ( !$ary ) {
			return $default;
		}

		if ( is_scalar( $ary ) ) {
			return is_string( $ary ) ? stripslashes( $ary ) : $ary;
		}

		if ( !is_countable( $ary ) ) {
			return $default;
		}

		$ret = [];
		$max = $limit;
		foreach ( $ary as $k => $v ) {
			if ( !is_scalar( $v ) ) {
				continue;
			}

			$ret[ $k ] = is_string( $v ) ? stripslashes( $v ) : $v;

			if ( $max-- < 1 ) {
				break;
			}
		}

		return $ret;
	}

}
