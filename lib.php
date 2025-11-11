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
 * Library of interface functions and constants.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Indicates API features that the module supports.
 *
 * @param string $feature FEATURE_xx constant.
 * @return bool|string|null True if the feature is supported, null otherwise.
 */
function zoomsdk_supports(string $feature): mixed {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COMMUNICATION;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the zoomsdk into the database.
 *
 * @param stdClass $moduleinstance An object from the form.
 * @param mod_zoomsdk_mod_form|null $mform The form.
 * @return int The id of the newly inserted record.
 */
function zoomsdk_add_instance(stdClass $moduleinstance, $mform = null): int {
    global $DB, $USER;

    $moduleinstance->timecreated = time();
    $moduleinstance->timemodified = time();

    // Verify Zoom configuration.
    $accountid = get_config('zoom', 'accountid') ?: get_config('mod_zoom', 'accountid');
    $clientid = get_config('zoom', 'clientid') ?: get_config('mod_zoom', 'clientid');
    $clientsecret = get_config('zoom', 'clientsecret') ?: get_config('mod_zoom', 'clientsecret');

    if (empty($accountid) || empty($clientid) || empty($clientsecret)) {
        throw new moodle_exception('zoom_not_configured', 'mod_zoomsdk');
    }

    // Get Zoom user.
    $zoomuser = zoomsdk_get_zoom_user($USER->email);
    if (!$zoomuser) {
        throw new moodle_exception('zoomusernotfound', 'mod_zoomsdk');
    }

    // Create meeting on Zoom.
    try {
        $meeting = zoomsdk_create_zoom_meeting($moduleinstance, $zoomuser->id);

        $moduleinstance->meetingid = (string)$meeting->id;
        $moduleinstance->hostid = $zoomuser->id;
        $moduleinstance->joinurl = $meeting->join_url;
        $moduleinstance->password = $meeting->password ?? '';
        
        // Store meeting type and start time.
        $moduleinstance->meeting_type = $moduleinstance->meeting_type ?? 2;
        $moduleinstance->start_time = $moduleinstance->start_time ?? null;

        // Store recurrence settings if applicable.
        if (in_array($moduleinstance->meeting_type, [3, 8])) {
            $moduleinstance->recurrence_type = $moduleinstance->recurrence_type ?? null;
            $moduleinstance->repeat_interval = $moduleinstance->repeat_interval ?? null;
            $moduleinstance->weekly_days = $moduleinstance->weekly_days ?? null;
            $moduleinstance->monthly_day = $moduleinstance->monthly_day ?? null;
            $moduleinstance->monthly_week = $moduleinstance->monthly_week ?? null;
            $moduleinstance->monthly_week_day = $moduleinstance->monthly_week_day ?? null;
            $moduleinstance->end_times = $moduleinstance->end_times ?? null;
            $moduleinstance->end_date_time = $moduleinstance->end_date_time ?? null;
        }

    } catch (Exception $e) {
        throw new moodle_exception('failedtocreatemeeting', 'mod_zoomsdk', '', null, $e->getMessage());
    }

    $id = $DB->insert_record('zoomsdk', $moduleinstance);
    $moduleinstance->id = $id;

    return $id;
}

/**
 * Updates an instance of the zoomsdk in the database.
 *
 * @param stdClass $moduleinstance An object from the form.
 * @param mod_zoomsdk_mod_form|null $mform The form.
 * @return bool True if successful, false otherwise.
 */
function zoomsdk_update_instance(stdClass $moduleinstance, $mform = null): bool {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // Store meeting type and start time.
    $moduleinstance->meeting_type = $moduleinstance->meeting_type ?? 2;
    $moduleinstance->start_time = $moduleinstance->start_time ?? null;

    // Store recurrence settings if applicable.
    if (in_array($moduleinstance->meeting_type, [3, 8])) {
        $moduleinstance->recurrence_type = $moduleinstance->recurrence_type ?? null;
        $moduleinstance->repeat_interval = $moduleinstance->repeat_interval ?? null;
        $moduleinstance->weekly_days = $moduleinstance->weekly_days ?? null;
        $moduleinstance->monthly_day = $moduleinstance->monthly_day ?? null;
        $moduleinstance->monthly_week = $moduleinstance->monthly_week ?? null;
        $moduleinstance->monthly_week_day = $moduleinstance->monthly_week_day ?? null;
        $moduleinstance->end_times = $moduleinstance->end_times ?? null;
        $moduleinstance->end_date_time = $moduleinstance->end_date_time ?? null;
    } else {
        // Clear recurrence fields for non-recurring meetings.
        $moduleinstance->recurrence_type = null;
        $moduleinstance->repeat_interval = null;
        $moduleinstance->weekly_days = null;
        $moduleinstance->monthly_day = null;
        $moduleinstance->monthly_week = null;
        $moduleinstance->monthly_week_day = null;
        $moduleinstance->end_times = null;
        $moduleinstance->end_date_time = null;
    }

    return $DB->update_record('zoomsdk', $moduleinstance);
}

/**
 * Removes an instance of the zoomsdk from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false otherwise.
 */
function zoomsdk_delete_instance(int $id): bool {
    global $DB;

    $zoomsdk = $DB->get_record('zoomsdk', ['id' => $id]);
    if (!$zoomsdk) {
        return false;
    }

    // Delete from Zoom.
    zoomsdk_delete_zoom_meeting($zoomsdk->meetingid);

    // Delete attendance records.
    $DB->delete_records('zoomsdk_attendance', ['zoomsdkid' => $id]);

    // Delete instance.
    $DB->delete_records('zoomsdk', ['id' => $id]);

    return true;
}
