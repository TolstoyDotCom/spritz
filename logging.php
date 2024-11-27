<?php

if ( !function_exists( 'rawdebug' ) ) {
	function rawdebug( ...$args ) {
		if ( WP_DEBUG ) {
			if ( count( $args ) < 2 ) {
				$arg = reset( $args );
				$arg = is_scalar( $arg ) ? '' . $arg : print_r( $arg, TRUE );
				error_log( $arg );
			}
			else {
				error_log( json_encode( $args ) );
			}
		}
	}
}
