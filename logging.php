<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !function_exists( 'rawdebug' ) ) {
	function rawdebug( ...$args ) {
		if ( WP_DEBUG ) {
			if ( count( $args ) < 2 ) {
				$arg = reset( $args );
				$arg = is_scalar( $arg ) ? '' . $arg : print_r( $arg, TRUE );
				error_log( $arg );
			}
			else {
				error_log( wp_json_encode( $args ) );
			}
		}
	}
}
