<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

?><input type="email" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $setting ); ?>" />
