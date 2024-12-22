<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

?><input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $setting ); ?> />
