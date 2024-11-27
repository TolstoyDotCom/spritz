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

use dev\wisdomtree\spritz\api\installation\IDirectories;

class Directories implements IDirectories {
	private $templatePaths;

	public function __construct( private readonly string $basePath ) {
		$this->templatePaths = [
			$this->buildPath( get_template_directory() ),
		];

		$temp = $this->buildPath( $this->basePath, 'templates' );
		if ( $temp ) {
			$this->templatePaths[] = $temp;
		}
	}

	public function getTemplatePath( string $key ) : ?string {
		foreach ( $this->templatePaths as $path ) {
			$temp = $this->buildPath( $path, $key . '.tpl.php' );
			if ( $temp ) {
				return $temp;
			}
		}

		return NULL;
	}

	public function buildPath( ...$args ) : ?string {
		$ret = '';

		foreach ( $args as $arg ) {
			$ret .= rtrim( rtrim( $arg ), '/' ) . '/';
		}

		if ( is_dir( $ret ) ) {
			return $ret;
		}

		$ret = rtrim( $ret, '/' );
		if ( is_file( $ret ) ) {
			return $ret;
		}

		return NULL;
	}
}
