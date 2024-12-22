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

namespace dev\wisdomtree\spritz\wordpress;

use dev\wisdomtree\spritz\api\installation\IDirectories;
use dev\wisdomtree\spritz\api\installation\ISchema;
use dev\wisdomtree\spritz\api\controller\ISpritzController;
use dev\wisdomtree\spritz\api\controller\ISettingsController;
use dev\wisdomtree\spritz\api\entity\ISpritzEntity;
use dev\wisdomtree\spritz\api\entity\SpritzState;
use dev\wisdomtree\spritz\api\utils\IUtils;
use dev\wisdomtree\spritz\wordpress\installation\Schema;
use dev\wisdomtree\spritz\wordpress\display\SpritzTableListing;
use dev\wisdomtree\spritz\wordpress\display\SpritzTableHistory;

class App {
	private const POST_STATE_MESSAGES = 'POST_STATE_MESSAGES';
	private const SECONDS_PER_DAY = 24 * 60 * 60;
	private const META_BOX_SAVE_POSSIBLE_INPUTS = [
		'spritz_post_sidebar_nonce',
		'spritz_override_post_settings',
		'spritz_confirm_override',
		'spritz_num_days',
		'spritz_auto_approve',
		'spritz_confirm_transition',
		'spritz_public_note',
		'spritz_private_note',
		'spritz_transition',
	];

	public function __construct( private readonly IDirectories $directories,
									private readonly ISpritzController $spritzController,
									private readonly ISettingsController $settingsController,
									private readonly IUtils $utils ) {
	}

	public function registerAdminPages() : void {
		add_menu_page(
			__('Spritz states', 'spritz'),
			__('Spritz states', 'spritz'),
			'manage_options',
			'spritz-listing',
			[ $this, 'showSpritzListingPage' ]
		);

		add_submenu_page(
			'spritz-listing',
			__('History', 'spritz'),
			'',
			'manage_options',
			'spritz-history',
			[ $this, 'showSpritzHistoryPage' ]
		);
	}

	public function removeUnusedSubmenus( $submenu_file ) : ?string {
		remove_submenu_page( 'spritz-listing', 'spritz-history' );

		return $submenu_file;
	}

	public function showSpritzListingPage() : void {
		$table = new SpritzTableListing( $this->directories, $this->spritzController, $this->settingsController, $this->utils );
		$table->prepare_items();
		$table->display();
	}

	public function showSpritzHistoryPage() : void {
		$table = new SpritzTableHistory( $this->directories, $this->spritzController, $this->settingsController, $this->utils );
		$table->prepare_items();
		$table->display();
	}

	public function registerSidebarBoxes() : void {
		add_meta_box( 'spritz_metabox',
						__('Spritz', 'spritz'),
						[ $this, 'generateSidebarBoxes' ],
						'post',
						'side',
						'high',
						[
							'__back_compat_meta_box' => FALSE,
						]
		);
	}

	public function generateSidebarBoxes( $post ) : void {
		$nonceField = wp_nonce_field( plugin_basename( __FILE__ ), 'spritz_post_sidebar_nonce', TRUE, FALSE );
		$messages = $this->getMessages();
		$message = NULL;
		$current_state_explanation = NULL;
		$transition_options = [];
		$path = $this->directories->getTemplatePath( 'sidebar_boxes' );
		if ( !$path ) {
			return;
		}

		if ( $post && !empty( $post->ID ) ) {
			$stateObjects = $this->getTransitionOptions( $post->ID );
			if ( $stateObjects ) {
				foreach ( $stateObjects as $stateObj ) {
					$transition_options[ $stateObj->name ] = $stateObj->value;
				}
			}
		}
		else {
			$messages[] = __('Post not found.', 'spritz');
		}

		if ( !$transition_options ) {
			$messages[] = __('You have no possible transitions.', 'spritz');
		}

		if ( $messages ) {
			$message = implode( ' & ', $messages );

			include( $path );

			return;
		}

		$spritzEntity = $this->spritzController->loadLatestByEntityId( $post->ID );

		$transition_label = __('Choose the new state for this post.', 'spritz');

		/* translators: The placeholder is a state name. */
		$current_state_explanation = sprintf( __('Current state: %s', 'spritz'), $spritzEntity->getCurrentState()->name );

		/* translators: The placeholder is a user-supplied note. */
		$current_state_public_note = sprintf( __('Note: %s', 'spritz'), $spritzEntity->getPublicNote() );

		/* translators: The placeholder is a local date. */
		$current_state_next_action_date = sprintf( __('Next action date: %s', 'spritz'), date( get_option( 'date_format' ), $spritzEntity->getNextReviewDate() ) );

		$public_note_label = __('A public note, shown to everyone.', 'spritz');
		$public_note_value = '';

		$private_note_label = __('A private note, only shown to admins.', 'spritz');
		$private_note_value = '';

		$confirm_transition = __('Click here to confirm the change.', 'spritz');

		$settingsEntity = $this->settingsController->load( $post->ID );

		$admin_panel_title = __('Override settings for this post.', 'spritz');
		$num_days_label = __('Number of days before review.', 'spritz');
		$show_admin_panel = current_user_can( 'spritz_override_post_settings' );

		/* translators: The placeholder is yes or no. */
		$auto_approve_text = sprintf( __('Auto-approve (default: %s).', 'spritz'), $settingsEntity->getAutomaticApprovals() ? __('yes', 'spritz') : __('no', 'spritz') );

		$spritz_num_days = $settingsEntity->getDaysBeforeReview();
		$confirm_override = __('Click here to confirm the change.', 'spritz');

		include( $path );
	}

	public function getPermissions() : array {
		$ret = [];

		foreach ( SpritzState::cases() as $fromState ) {
			foreach ( SpritzState::cases() as $toState ) {
				if ( $fromState === $toState ) {
					continue;
				}

				$key = $this->transitionToPermission( $fromState, $toState );

				/* translators: Both placeholders are spritz states. */
				$val = sprintf( __('Spritz: transition posts from the \'%1$s\' state to the \'%2$s\' state', 'spritz'), $fromState->value, $toState->value );

				$ret[ $key ] = $val;
			}
		}

		$ret[ 'spritz_edit_settings' ] = __('Spritz: edit settings', 'spritz');

		$ret[ 'spritz_override_post_settings' ] = __('Spritz: override settings for specific posts', 'spritz');

		return $ret;
	}

	public function install() : void {
		$this->createCustomTables();
		$this->createPermissions();
		$this->createCronTask();
	}

	public function deactivate() : void {
	}

	public function uninstall() : void {
		$this->deleteCustomTables();
		$this->settingsController->deleteSettings();
		$this->deletePermissions();
		$this->deleteCronTask();
	}

	public function registerHooks() : void {
		add_action( 'add_meta_boxes', [ $this, 'registerSidebarBoxes' ] );
		add_action( 'save_post', [ $this, 'metaBoxSavePost' ], 10, 2 );
		add_action( 'admin_menu', [ $this, 'registerAdminPages' ] );
		add_action( 'spritz_presave_spritz_entity', [ $this, 'preSaveSpritzEntity' ] );
		add_action( 'spritz_cron_task', [ $this, 'cron' ] );
		add_filter( 'submenu_file', [ $this, 'removeUnusedSubmenus' ] );
		add_shortcode( 'spritz_post_status', [ $this, 'shortcodePostStatus' ] );
		add_shortcode( 'spritz_post_note', [ $this, 'shortcodePostNote' ] );
		add_shortcode( 'spritz_post_nextreviewdate', [ $this, 'shortcodePostNextReviewDate' ] );
	}

	public function shortcodePostStatus( $atts, $content = '' ) : string {
		$spritzEntity = $this->spritzController->loadLatestByEntityId( get_the_ID(), FALSE );

		return $spritzEntity ? $spritzEntity->getCurrentState()->value : '';
	}

	public function shortcodePostNote( $atts, $content = '' ) : string {
		$spritzEntity = $this->spritzController->loadLatestByEntityId( get_the_ID(), FALSE );

		return $spritzEntity ? $spritzEntity->getPublicNote() : '';
	}

	public function shortcodePostNextReviewDate( $atts, $content = '' ) : string {
		$spritzEntity = $this->spritzController->loadLatestByEntityId( get_the_ID(), FALSE );

		return $spritzEntity ? date( get_option( 'date_format' ), $spritzEntity->getNextReviewDate() ) : '';
	}

	public function cron() : void {
		$numProcessed = $this->spritzController->processExpiredReviews();
		if ( $numProcessed > 0 ) {
			$settingsEntity = $this->settingsController->load( 0 );

			$tokens = [
				'[numberOfChanges]' => $numProcessed,
			];

			$addr = strtr( trim( $settingsEntity->getNotificationEmailAddress() ), $tokens );
			$subj = strtr( trim( $settingsEntity->getNotificationEmailSubject() ), $tokens );
			$body = strtr( trim( $settingsEntity->getNotificationEmailBody() ), $tokens );

			if ( $addr && $subj && $body ) {
				wp_mail( $addr, $subj, $body );
			}
		}
	}

	public function preSaveSpritzEntity( $entity ) : void {
		if ( !$entity || !$entity->getEntityId() ) {
			return;
		}

		if ( $entity->getCurrentState() !== SpritzState::REVIEWED ) {
			return;
		}

		$settingsEntity = $this->settingsController->load( $entity->getEntityId() );
		if ( $settingsEntity && $settingsEntity->getAutomaticApprovals() ) {
			$entity->setCurrentState( SpritzState::APPROVED );
		}
	}

	public function metaBoxSavePost( $postId, $post ) : int {
		$input = $this->utils->stripSlashesScalarKeys( $_POST, self::META_BOX_SAVE_POSSIBLE_INPUTS );

		if ( empty( $input[ 'spritz_post_sidebar_nonce' ] ) || !wp_verify_nonce( $input[ 'spritz_post_sidebar_nonce' ], plugin_basename( __FILE__ ) ) ) {
			return $postId;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $postId;
		}

		$messages = $this->processPostOverrides( $postId, $input );
		$messages += $this->processPostTransition( $postId, $input );

		$this->setMessages( $messages );

		rawdebug( 'messages: ', implode( ' ;; ', $messages ) );

		return $postId;
	}

	protected function createCronTask() : void {
		if ( !wp_next_scheduled( 'spritz_cron_task' ) ) {
			wp_schedule_event( time(), 'daily', 'spritz_cron_task' );
		}
	}

	protected function deleteCronTask() : void {
		$when = wp_next_scheduled( 'spritz_cron_task' );

		if ( $when ) {
			wp_unschedule_event( $when, 'spritz_cron_task' );
		}
	}

	protected function deleteCustomTables() : void {
		global $wpdb;

		$schema = new Schema( $wpdb->prefix, $wpdb->get_charset_collate() );
		$commands = $schema->getDeleteCommands();

		foreach ( $commands as $command ) {
			$wpdb->query( $command );
		}
	}

	protected function processPostOverrides( $postId, $input ) : array {
		try {
			if ( $postId < 1 ) {
				return [];
			}

			if ( !current_user_can( 'spritz_override_post_settings' ) ) {
				return [];
			}

			$override = $this->getCheckboxValue( 'spritz_confirm_override', $input );
			if ( !$override ) {
				return [];
			}

			if ( empty( $input[ 'spritz_num_days' ] ) || !ctype_digit( '' . $input[ 'spritz_num_days' ] ) ) {
				return [ __('Invalid values.', 'spritz') ];
			}

			$autoApprove = $this->getCheckboxValue( 'spritz_auto_approve', $input );

			$settingsEntity = $this->settingsController->load( $postId );

			$settingsEntity->setDaysBeforeReview( (int) $input[ 'spritz_num_days' ] );
			$settingsEntity->setAutomaticApprovals( $autoApprove );

			$this->settingsController->save( $settingsEntity );

			return [ __('Overrides saved.', 'spritz') ];
		}
		catch ( Exception $e ) {
			return [ $e->getMessage() ];
		}
	}

	protected function processPostTransition( $postId, $input ) : array {
		try {
			if ( $postId < 1 ) {
				return [];
			}

			$confirm = $this->getCheckboxValue( 'spritz_confirm_transition', $input );
			if ( !$confirm ) {
				return [];
			}

			if ( empty( $input[ 'spritz_public_note' ] ) ) {
				return [ __('You must provide a public note.', 'spritz') ];
			}

			if ( !array_key_exists( 'spritz_private_note', $input ) ) {
				return [];
			}

			if ( empty( $input[ 'spritz_transition' ] ) ) {
				return [];
			}

			$publicNote = sanitize_text_field( $input[ 'spritz_public_note' ] );
			$privateNote = sanitize_text_field( $input[ 'spritz_private_note' ] );

			$toState = SpritzState::tryFrom( $input[ 'spritz_transition' ] );
			if ( !$toState ) {
				return [];
			}

			$settingsEntity = $this->settingsController->load( $postId );
			if ( !$settingsEntity ) {
				return [ __('Settings entity can not be loaded.', 'spritz') ];
			}

			$spritzEntity = $this->spritzController->loadLatestByEntityId( $postId );
			if ( !$spritzEntity ) {
				return [ __('Post entity can not be loaded.', 'spritz') ];
			}

			if ( $toState === $spritzEntity->getCurrentState() ) {
				return [ __('From state and to state are the same.', 'spritz') ];
			}

			$perm = $this->transitionToPermission( $spritzEntity->getCurrentState(), $toState );
			if ( !current_user_can( $perm ) ) {
				return [ __('You do not have the rights to perform that specific transition.', 'spritz') ];
			}

			$reviewDate = time() + ( self::SECONDS_PER_DAY * $settingsEntity->getDaysBeforeReview() );

			$newEntity = $this->spritzController->createSpritzEntity( get_current_user_id(), $postId, 'post', $toState, time(), $reviewDate, $publicNote, $privateNote );
			if ( !$newEntity ) {
				return [ __('Post entity could not be created.', 'spritz') ];
			}

			$this->spritzController->save( $newEntity );

			return [ __('Transition saved.', 'spritz') ];
		}
		catch ( Exception $e ) {
			return [ $e->getMessage() ];
		}
	}

	protected function createCustomTables() : void {
		global $wpdb;

		$schema = new Schema( $wpdb->prefix, $wpdb->get_charset_collate() );

		$commands = $schema->getCreateCommands();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $commands );
	}

	protected function createPermissions() : void {
		$role = get_role( 'administrator' );
		if ( !$role ) {
			throw new Error( 'you need to create an administrator role' );
		}

		foreach ( $this->getPermissions() as $perm => $name ) {
			$role->add_cap( $perm );
		}
	}

	protected function deletePermissions() : void {
		$perms = $this->getPermissions();
		$roleManager = wp_roles();

		foreach ( get_editable_roles() as $roleName => $ary ) {
			foreach ( SpritzState::cases() as $toState ) {
				$perm = 'spritz_transition_to_' . strtolower( $toState->value );
				$roleManager->remove_cap( $roleName, $perm );
			}

			foreach ( $perms as $perm ) {
				$roleManager->remove_cap( $roleName, $perm );
			}
		}
	}

	protected function getTransitionOptions( $id ) : array {
		$spritzEntity = $this->spritzController->loadLatestByEntityId( $id );
		$currentState = $spritzEntity->getCurrentState();

		$userPerms = [];
		foreach ( SpritzState::cases() as $toState ) {
			if ( $currentState === $toState ) {
				continue;
			}

			$key = $this->transitionToPermission( $currentState, $toState );
			if ( current_user_can( $key ) ) {
				$userPerms[] = $toState;
			}
		}

		return $userPerms;
	}

	protected function transitionToPermission( SpritzState $fromState, SpritzState $toState ) : string {
		return 'spritz_transition_from_' . strtolower( $fromState->value ) . '_to_' . strtolower( $toState->value );
	}

	protected function getMessages() : array {
		return isset( $_SESSION[ self::POST_STATE_MESSAGES ] ) ? $this->utils->stripSlashesScalarLimit( $_SESSION[ self::POST_STATE_MESSAGES ], 20, [] ) : [];
	}

	protected function setMessages( $messages = [] ) : void {
		if ( !isset( $_SESSION[ self::POST_STATE_MESSAGES ] ) ) {
			$_SESSION[ self::POST_STATE_MESSAGES ] = [];
		}

		$_SESSION[ self::POST_STATE_MESSAGES ] = array_merge( $messages, $this->utils->stripSlashesScalarLimit( $_SESSION[ self::POST_STATE_MESSAGES ], 20, [] ) );
	}

	protected function getCheckboxValue( $key, $ary ) : bool {
		return !empty( $ary[ $key ] ) && $ary[ $key ] === 'on';
	}
}
