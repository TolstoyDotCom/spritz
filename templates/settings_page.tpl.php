<form action="options.php" method="POST">
	<?php settings_fields( 'spritz_options_group' ); ?>
	<?php do_settings_sections( 'spritz' ); ?>
	<?php echo esc_html( $emailHelp ); ?>
	<?php submit_button(); ?>
</form>
