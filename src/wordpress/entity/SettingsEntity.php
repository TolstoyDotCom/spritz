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

namespace dev\wisdomtree\spritz\wordpress\entity;

use dev\wisdomtree\spritz\api\entity\ISettingsEntity;

class SettingsEntity extends BaseEntity implements ISettingsEntity {
	protected $automatic_approvals;
	protected $days_before_review;
	protected $notification_email_address;
	protected $notification_email_subject;
	protected $notification_email_body;

	private const FIELDS = [
		'id' => [ 't' => 'int', 'd' => 0 ],
		'automatic_approvals' => [ 't' => 'bool', 'd' => FALSE ],
		'days_before_review' => [ 't' => 'int', 'd' => 180 ],
		'notification_email_address' => [ 't' => 'string', 'd' => '' ],
		'notification_email_subject' => [ 't' => 'string', 'd' => '' ],
		'notification_email_body' => [ 't' => 'string', 'd' => '' ],
	];

	public function __construct( array $data = [] ) {
		parent::__construct( $data );
	}

	public function getId() : int { return $this->id; }
	public function getAutomaticApprovals() : bool { return $this->automatic_approvals; }
	public function setAutomaticApprovals( bool $val ) : void { $this->automatic_approvals = $val; }
	public function getDaysBeforeReview() : int { return $this->days_before_review; }
	public function setDaysBeforeReview( int $val ) : void { $this->days_before_review = $val; }

	public function getNotificationEmailAddress() : string { return $this->notification_email_address; }
	public function getNotificationEmailSubject() : string { return $this->notification_email_subject; }
	public function getNotificationEmailBody() : string { return $this->notification_email_body; }

	protected function getFields() : array {
		return self::FIELDS;
	}
}
