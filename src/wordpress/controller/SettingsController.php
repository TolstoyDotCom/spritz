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

namespace dev\wisdomtree\spritz\wordpress\controller;

use dev\wisdomtree\spritz\api\entity\ISettingsEntity;
use dev\wisdomtree\spritz\api\installation\IDirectories;
use dev\wisdomtree\spritz\api\controller\ISettingsController;
use dev\wisdomtree\spritz\wordpress\entity\SettingsEntity;

class SettingsController implements ISettingsController {
	private const POST_META_KEY = 'spritz_postmeta';
	private const SETTINGS_OPTIONS_GROUP = 'spritz_options_group';
	private const SETTINGS_KEY_APPROVALS = 'spritz_setting_automatic_approvals';
	private const SETTINGS_KEY_DAYS_BEFORE = 'spritz_setting_days_before_review';
	private const SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS = 'spritz_setting_notification_email_address';
	private const SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT = 'spritz_setting_notification_email_subject';
	private const SETTINGS_KEY_NOTIFICATION_EMAIL_BODY = 'spritz_setting_notification_email_body';

	public function __construct(
		private readonly IDirectories $directories,
		private readonly object $dbConnection,
		private readonly string $dbPrefix,
		private readonly string $dbCollation
	) {}

	public function save( ISettingsEntity $entity ) : bool {
		if ( !$entity ) {
			return FALSE;
		}

		if ( $entity->getId() < 1 ) {
			update_option( self::SETTINGS_KEY_APPROVALS, (bool) $entity->getAutomaticApprovals() );
			update_option( self::SETTINGS_KEY_DAYS_BEFORE, (int) $entity->getDaysBeforeReview() );
		}
		else {
			$res = update_post_meta( $entity->getId(), self::POST_META_KEY, $entity->toArray() );
		}

		return TRUE;
	}

	public function load( int $id ) : ISettingsEntity {
		$data = [
			'id' => $id,
			'automatic_approvals' => get_option( self::SETTINGS_KEY_APPROVALS ),
			'days_before_review' => get_option( self::SETTINGS_KEY_DAYS_BEFORE ),
			'notification_email_address' => get_option( self::SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS ),
			'notification_email_subject' => get_option( self::SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT ),
			'notification_email_body' => get_option( self::SETTINGS_KEY_NOTIFICATION_EMAIL_BODY ),
		];

		if ( $id > 0 ) {
			$overrides = get_post_meta( $id, self::POST_META_KEY, TRUE );
			$overrides = is_array( $overrides ) && !empty( $overrides ) ? $overrides : [];
			$data = $overrides + $data;
		}

		return new SettingsEntity( $data );
	}

	public function deleteSettings() : void {
		delete_option( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_APPROVALS );
		delete_option( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_DAYS_BEFORE );
		delete_option( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS );
		delete_option( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT );
		delete_option( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_NOTIFICATION_EMAIL_BODY );
	}

	public function registerHooks() : void {
		add_action( 'admin_menu', [ $this, 'addMenu' ] );
		add_action( 'admin_init', [ $this, 'registerSettingsPage' ] );
	}

	public function addMenu() : void {
		add_options_page( __('Spritz Settings', 'spritz'), __('Spritz', 'spritz'), 'manage_options', 'spritz', [ $this, 'settingsPage' ] );
	}

	public function settingsPage() : void {
		$emailHelp = __('In the email subject and/or body, you can use "[numberOfChanges]" to insert the number of Spritzen that were changed.', 'spritz');
		$linksHelp = __('Links:', 'spritz');

		$link1url = 'https://wisdomtree.dev/spritz-wordpress-plugin';
		$link1text = __('Spritz plugin homepage.', 'spritz');

		$link2url = 'https://www.paypal.com/donate/?hosted_button_id=4U3VYC5LNWRM4';
		$link2text = __('Help fund Spritz plugin development.', 'spritz');

		$optionsGroup = self::SETTINGS_OPTIONS_GROUP;

		$path = $this->directories->getTemplatePath( 'settings_page' );
		if ( $path ) {
			include( $path );
		}
	}

	public function registerSettingsPage() : void {
		register_setting( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_APPROVALS, [
			'type' => 'boolean',
			'sanitize_callback' => [$this, 'sanitizeBoolean'],
		]);

		register_setting( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_DAYS_BEFORE, [
			'type' => 'integer',
			'sanitize_callback' => [$this, 'sanitizeInt'],
		]);

		register_setting( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS, [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field'
		]);

		register_setting( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT, [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field'
		]);

		register_setting( self::SETTINGS_OPTIONS_GROUP, self::SETTINGS_KEY_NOTIFICATION_EMAIL_BODY, [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field'
		]);

		add_settings_section( 'spritz_section', __('Spritz Settings', 'spritz'), [ $this, 'settingsSectionCallback' ], 'spritz' );
		add_settings_field( self::SETTINGS_KEY_APPROVALS, __('Automatically approve posts', 'spritz'), [ $this, 'settingsFieldApprovalsCallback' ], 'spritz', 'spritz_section' );
		add_settings_field( self::SETTINGS_KEY_DAYS_BEFORE, __('Number of days before a post is reviewed', 'spritz'), [ $this, 'settingsFieldDaysBeforeCallback' ], 'spritz', 'spritz_section' );
		add_settings_field( self::SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS, __('Daily notification emails will be sent to this address', 'spritz'), [ $this, 'settingsFieldEmailAddressCallback' ], 'spritz', 'spritz_section' );
		add_settings_field( self::SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT, __('The subject for notification emails.', 'spritz'), [ $this, 'settingsFieldEmailSubjectCallback' ], 'spritz', 'spritz_section' );
		add_settings_field( self::SETTINGS_KEY_NOTIFICATION_EMAIL_BODY, __('The body for notification emails.', 'spritz'), [ $this, 'settingsFieldEmailBodyCallback' ], 'spritz', 'spritz_section' );
	}

	public function settingsSectionCallback() : void {
		$title = __('Settings for the Spritz plugin.', 'spritz');

		$path = $this->directories->getTemplatePath( 'settings_section_header' );
		if ( $path ) {
			include( $path );
		}
	}

	public function settingsFieldApprovalsCallback() : void {
		$name = self::SETTINGS_KEY_APPROVALS;
		$setting = get_option( self::SETTINGS_KEY_APPROVALS );

		$path = $this->directories->getTemplatePath( 'settings_field_approval' );
		if ( $path ) {
			include( $path );
		}
	}

	public function settingsFieldDaysBeforeCallback() : void {
		$name = self::SETTINGS_KEY_DAYS_BEFORE;
		$setting = get_option( self::SETTINGS_KEY_DAYS_BEFORE );

		$path = $this->directories->getTemplatePath( 'settings_field_days_before' );
		if ( $path ) {
			include( $path );
		}
	}

	public function settingsFieldEmailAddressCallback() : void {
		$name = self::SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS;
		$setting = get_option( self::SETTINGS_KEY_NOTIFICATION_EMAIL_ADDRESS );

		$path = $this->directories->getTemplatePath( 'settings_field_email_address' );
		if ( $path ) {
			include( $path );
		}
	}

	public function settingsFieldEmailSubjectCallback() : void {
		$name = self::SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT;
		$setting = get_option( self::SETTINGS_KEY_NOTIFICATION_EMAIL_SUBJECT );

		$path = $this->directories->getTemplatePath( 'settings_field_email_subject' );
		if ( $path ) {
			include( $path );
		}
	}

	public function settingsFieldEmailBodyCallback() : void {
		$name = self::SETTINGS_KEY_NOTIFICATION_EMAIL_BODY;
		$setting = get_option( self::SETTINGS_KEY_NOTIFICATION_EMAIL_BODY );

		$path = $this->directories->getTemplatePath( 'settings_field_email_body' );
		if ( $path ) {
			include( $path );
		}
	}

	public function sanitizeBoolean( $val ) : bool {
		return $val ? TRUE : FALSE;
	}

	public function sanitizeInt( $val ) : bool {
		return (int) $val;
	}
}
