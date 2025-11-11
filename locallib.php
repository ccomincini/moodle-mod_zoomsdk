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
 * Internal library of functions for module zoomsdk.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get Zoom user by email.
 *
 * @param string $email User email address.
 * @return stdClass|false Zoom user object or false.
 */
function zoomsdk_get_zoom_user(string $email) {
    global $CFG;
    
    // Check if mod_zoom is installed.
    if (!file_exists($CFG->dirroot . '/mod/zoom/classes/webservice.php')) {
        debugging('mod_zoom plugin not found', DEBUG_DEVELOPER);
        return false;
    }
    
    require_once($CFG->dirroot . '/mod/zoom/classes/webservice.php');
    
    try {
        $service = new \mod_zoom\webservice();
        return $service->get_user($email);
    } catch (Exception $e) {
        debugging('Failed to get Zoom user: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }
}

/**
 * Create a Zoom meeting via API.
 *
 * @param stdClass $data Meeting data from form.
 * @param string $hostuserid Zoom host user ID.
 * @return stdClass Zoom API response.
 * @throws moodle_exception If API call fails.
 */
function zoomsdk_create_zoom_meeting(stdClass $data, string $hostuserid): stdClass {
    global $CFG;
    
    require_once($CFG->dirroot . '/mod/zoom/classes/webservice.php');
    
    $service = new \mod_zoom\webservice();

    $meetingdata = [
        'topic' => $data->name,
        'type' => $data->meeting_type ?? 2, // Default to scheduled.
        'duration' => (int) ceil($data->duration / 60), // Convert seconds to minutes.
        'settings' => [
            'host_video' => true,
            'participant_video' => true,
            'join_before_host' => true,
            'mute_upon_entry' => false,
            'waiting_room' => false,
            'auto_recording' => 'none',
        ],
    ];

    // Add start_time for scheduled and recurring with fixed time.
    if (in_array($data->meeting_type, [2, 3]) && !empty($data->start_time)) {
        $meetingdata['start_time'] = gmdate('Y-m-d\TH:i:s\Z', $data->start_time);
    }

    // Add recurrence settings for recurring meetings.
    if (in_array($data->meeting_type, [3, 8])) {
        $recurrence = [
            'type' => $data->recurrence_type ?? 1, // Default to daily.
        ];

        // Repeat interval.
        if (!empty($data->repeat_interval)) {
            $recurrence['repeat_interval'] = (int) $data->repeat_interval;
        }

        // Weekly days.
        if ($data->recurrence_type == 2 && !empty($data->weekly_days)) {
            $recurrence['weekly_days'] = $data->weekly_days;
        }

        // Monthly day.
        if ($data->recurrence_type == 3 && !empty($data->monthly_day)) {
            $recurrence['monthly_day'] = (int) $data->monthly_day;
        }

        // Monthly week.
        if ($data->recurrence_type == 3 && !empty($data->monthly_week)) {
            $recurrence['monthly_week'] = (int) $data->monthly_week;
        }

        // Monthly week day.
        if ($data->recurrence_type == 3 && !empty($data->monthly_week_day)) {
            $recurrence['monthly_week_day'] = (int) $data->monthly_week_day;
        }

        // End times or end date time.
        if (!empty($data->end_times)) {
            $recurrence['end_times'] = (int) $data->end_times;
        } else if (!empty($data->end_date_time)) {
            $recurrence['end_date_time'] = gmdate('Y-m-d\TH:i:s\Z', $data->end_date_time);
        }

        $meetingdata['recurrence'] = $recurrence;
    }

    $url = "users/{$hostuserid}/meetings";

    try {
        // Use reflection to access protected method.
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('make_call');
        $method->setAccessible(true);
        $response = $method->invoke($service, $url, $meetingdata, 'post');

        // Verify response contains necessary data.
        if (empty($response->id)) {
            throw new moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 'Meeting ID is empty in API response');
        }

        return $response;
    } catch (Exception $e) {
        throw new moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, $e->getMessage());
    }
}

/**
 * Delete a Zoom meeting via API.
 *
 * @param string $meetingid Zoom meeting ID.
 * @return void
 */
function zoomsdk_delete_zoom_meeting(string $meetingid): void {
    global $CFG;
    
    if (!file_exists($CFG->dirroot . '/mod/zoom/classes/webservice.php')) {
        debugging('mod_zoom plugin not found', DEBUG_DEVELOPER);
        return;
    }
    
    require_once($CFG->dirroot . '/mod/zoom/classes/webservice.php');
    
    try {
        $service = new \mod_zoom\webservice();
        $service->delete_meeting($meetingid, false);
    } catch (Exception $e) {
        // Ignore error if meeting doesn't exist anymore.
        $message = $e->getMessage();
        // Check if error is "meeting not found" (code 3001 or 300).
        if (strpos($message, '3001') === false && strpos($message, '300') === false) {
            debugging("Failed to delete Zoom meeting: " . $message, DEBUG_NORMAL);
        }
    }
}
