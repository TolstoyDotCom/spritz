<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

?><form action="options.php" method="POST">
	<?php settings_fields( $optionsGroup ); ?>
	<?php do_settings_sections( 'spritz' ); ?>
	<?php echo esc_html( $emailHelp ); ?>
	<br/>
	<br/>
	<h4><?php echo esc_html( $linksHelp ); ?></h4>
	<ul>
		<li><a href="<?php echo esc_url( $link1url ); ?>"><?php echo esc_html( $link1text ); ?></a></li>
		<li><a href="<?php echo esc_url( $link2url ); ?>"><?php echo esc_html( $link2text ); ?></a></li>
	</ul>
	<br/>
	<?php submit_button(); ?>
</form>
