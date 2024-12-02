<?php
/*
Plugin Name: Spritz
Description: A WordPress plugin for posts that need to be reviewed on a regular basis. This plugin adds basic workflow states to posts.
Version: 1.0.0
Author: tolstoydotcom / Chris Kelly
Author URI: https://wisdomtree.dev/
Text Domain: spritz
Requires PHP: 8.3
License: Apache-2.0
*/

use dev\wisdomtree\spritz\wordpress\App;
use dev\wisdomtree\spritz\wordpress\installation\Directories;
use dev\wisdomtree\spritz\wordpress\installation\Schema;
use dev\wisdomtree\spritz\wordpress\controller\SpritzController;
use dev\wisdomtree\spritz\wordpress\controller\SettingsController;
use dev\wisdomtree\spritz\wordpress\entity\SpritzEntity;
use dev\wisdomtree\spritz\wordpress\entity\SpritzState;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/logging.php';

class SpritzStarter {
	public function __construct() {
	}

	public function run() {
		global $wpdb;

		$directories = new Directories( __DIR__ );
		$spritzController = new SpritzController( $wpdb, $wpdb->prefix, $wpdb->get_charset_collate() );
		$settingsController = new SettingsController( $directories, $wpdb, $wpdb->prefix, $wpdb->get_charset_collate() );
		$app = new App( $directories, $spritzController, $settingsController );

		$spritzController->registerHooks();
		$settingsController->registerHooks();

		register_activation_hook( __FILE__, [ $app, 'install' ] );
		register_deactivation_hook( __FILE__, [ $app, 'deactivate' ] );

		$app->registerHooks();
	}
}

function spritz_uninstall() {
	global $wpdb;

	$directories = new Directories( __DIR__ );
	$spritzController = new SpritzController( $wpdb, $wpdb->prefix, $wpdb->get_charset_collate() );
	$settingsController = new SettingsController( $directories, $wpdb, $wpdb->prefix, $wpdb->get_charset_collate() );
	$app = new App( $directories, $spritzController, $settingsController );
	$app->uninstall();
}

( new SpritzStarter() )->run();

register_uninstall_hook( __FILE__, 'spritz_uninstall' );
