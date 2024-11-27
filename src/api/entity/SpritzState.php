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

namespace dev\wisdomtree\spritz\api\entity;

enum SpritzState : string {
	case NEUTRAL = 'NEUTRAL';
	case AWAITING_REVIEW = 'AWAITING_REVIEW';
	case REVIEWED = 'REVIEWED';
	case NEEDS_WORK = 'NEEDS_WORK';
	case APPROVED = 'APPROVED';
	case DISAPPROVED = 'DISAPPROVED';
}
