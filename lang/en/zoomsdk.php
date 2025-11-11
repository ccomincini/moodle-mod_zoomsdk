<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin.
$string['pluginname'] = 'Zoom SDK Meeting';
$string['modulename'] = 'Zoom SDK Meeting';
$string['modulenameplural'] = 'Zoom SDK Meetings';
$string['modulename_help'] = 'Use the Zoom SDK Meeting module to host Zoom meetings embedded directly in Moodle.';
$string['pluginadministration'] = 'Zoom SDK administration';

// Form.
$string['meetingname'] = 'Meeting name';
$string['meetingsettings'] = 'Meeting settings';
$string['starttime'] = 'Start time';
$string['duration'] = 'Duration';
$string['meetingid'] = 'Meeting ID';

// Validation errors.
$string['starttime_past'] = 'Start time cannot be in the past';
$string['duration_toolow'] = 'Duration must be at least 1 minute';

// View.
$string['joinmeeting'] = 'Join Meeting (Embedded)';
$string['connecting'] = 'Connecting...';

// Settings.
$string['sdkkey'] = 'SDK Key';
$string['sdkkey_desc'] = 'Your Zoom Meeting SDK Key from marketplace.zoom.us';
$string['sdksecret'] = 'SDK Secret';
$string['sdksecret_desc'] = 'Your Zoom Meeting SDK Secret (keep confidential!)';

// Errors.
$string['zoomusernotfound'] = 'Zoom user not found for your email';
$string['failedtocreatemeeting'] = 'Failed to create Zoom meeting';
$string['zoom_not_configured'] = 'Zoom API is not configured. Please contact your administrator.';
$string['signaturefailed'] = 'Failed to get Zoom signature';
$string['joinfailed'] = 'Failed to join meeting';
$string['sdkinitfailed'] = 'Failed to initialize Zoom SDK';
$string['apicallfailed'] = 'Zoom API call failed';

// Privacy.
$string['privacy:metadata:zoomsdk_attendance'] = 'Information about user attendance at Zoom meetings';
$string['privacy:metadata:zoomsdk_attendance:userid'] = 'User ID';
$string['privacy:metadata:zoomsdk_attendance:jointime'] = 'Time user joined meeting';
$string['privacy:metadata:zoomsdk_attendance:leavetime'] = 'Time user left meeting';
$string['privacy:metadata:zoomsdk_attendance:duration'] = 'Duration of attendance in seconds';
$string['privacy:metadata:zoom'] = 'User data sent to Zoom for meeting participation';
$string['privacy:metadata:zoom:fullname'] = 'Full name displayed in meeting';
$string['privacy:metadata:zoom:email'] = 'Email address for identification';

// Capabilities.
$string['zoomsdk:addinstance'] = 'Add a new Zoom SDK meeting';
$string['zoomsdk:view'] = 'View Zoom SDK meeting';
$string['zoomsdk:viewattendance'] = 'View meeting attendance';

// Scheduled tasks.
$string['task_fetch_attendance'] = 'Fetch Zoom attendance records';

$string['task_cleanup_deleted'] = 'Clean up deleted Zoom SDK modules';
