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

// Meeting type.
$string['meetingtype'] = 'Meeting type';
$string['meetingtype_help'] = 'Choose the type of meeting to create';
$string['type_instant'] = 'Instant meeting';
$string['type_scheduled'] = 'Scheduled meeting';
$string['type_recurring_fixed'] = 'Recurring meeting with fixed time';
$string['type_recurring_nofixed'] = 'Recurring meeting without fixed time';

// Recurrence settings.
$string['recurrencesettings'] = 'Recurrence settings';
$string['recurrence_type'] = 'Recurrence type';
$string['recurrence_type_help'] = 'How often the meeting repeats';
$string['recurrence_daily'] = 'Daily';
$string['recurrence_weekly'] = 'Weekly';
$string['recurrence_monthly'] = 'Monthly';
$string['repeat_interval'] = 'Repeat every';
$string['repeat_interval_help'] = 'Number of days/weeks/months between occurrences';
$string['weekly_days'] = 'Repeat on';
$string['weekly_days_help'] = 'Select the days of the week for the meeting';
$string['day_sunday'] = 'Sunday';
$string['day_monday'] = 'Monday';
$string['day_tuesday'] = 'Tuesday';
$string['day_wednesday'] = 'Wednesday';
$string['day_thursday'] = 'Thursday';
$string['day_friday'] = 'Friday';
$string['day_saturday'] = 'Saturday';
$string['monthly_day'] = 'Day of month';
$string['monthly_day_help'] = 'Day of the month (1-31)';
$string['monthly_week'] = 'Week of month';
$string['monthly_week_help'] = 'Which week of the month';
$string['monthly_week_day'] = 'Day of week';
$string['monthly_week_day_help'] = 'Which day of the week';
$string['week_last'] = 'Last';
$string['week_first'] = 'First';
$string['week_second'] = 'Second';
$string['week_third'] = 'Third';
$string['week_fourth'] = 'Fourth';
$string['end_times'] = 'Number of occurrences';
$string['end_times_help'] = 'How many times the meeting will occur (max 60)';
$string['end_date_time'] = 'End by date';
$string['end_date_time_help'] = 'Last date for recurring meetings';

// Validation errors.
$string['starttime_past'] = 'Start time cannot be in the past';
$string['duration_toolow'] = 'Duration must be at least 1 minute';
$string['err_weekly_days'] = 'You must select at least one day for weekly recurrence';
$string['err_end_times'] = 'Number of occurrences must be between 1 and 60';
$string['err_repeat_interval'] = 'Repeat interval must be at least 1';
$string['err_monthly_day'] = 'Day of month must be between 1 and 31';

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
