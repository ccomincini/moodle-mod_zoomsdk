<?php
// This file is part of Moodle - http://moodle.org/
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
 * Aggiornato per supportare tutte le nuove opzioni sicurezza/media/ricorrenza del form
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

function zoomsdk_add_instance(stdClass $moduleinstance, $mform = null): int {
    global $DB, $USER;
    $moduleinstance->timecreated = time();
    $moduleinstance->timemodified = time();

    // ... verifica config Zoom ...
    $zoomuser = zoomsdk_get_zoom_user($USER->email);
    if (!$zoomuser) {
        // Log dettagliato per debug
        error_log('=== ZOOM USER NOT FOUND ===');
        error_log('Email cercata: ' . $USER->email);
        error_log('User Moodle: ' . $USER->firstname . ' ' . $USER->lastname);
        error_log('===========================');
        
        throw new moodle_exception('zoomusernotfound', 'mod_zoomsdk', '', null,
            'Utente Zoom non trovato per email: ' . $USER->email . '. ' .
            'Verifica che questa email sia registrata come utente su Zoom e che le credenziali API siano corrette.');
    }
    
    // Passa tutte le nuove opzioni a zoomsdk_create_zoom_meeting (vedi sotto)
    try {
        $meeting = zoomsdk_create_zoom_meeting($moduleinstance, $zoomuser->id);
        $moduleinstance->meetingid = (string)$meeting->id;
        $moduleinstance->hostid = $zoomuser->id;
        $moduleinstance->joinurl = $meeting->join_url;
        $moduleinstance->password = $meeting->password ?? '';
        // Salva anche tutte le nuove opzioni sicurezza/media del form
        $moduleinstance->option_waiting_room = $moduleinstance->option_waiting_room ?? 0;
        $moduleinstance->option_jbh = $moduleinstance->option_jbh ?? 0;
        $moduleinstance->option_authenticated_users = $moduleinstance->option_authenticated_users ?? 0;
        $moduleinstance->option_host_video = $moduleinstance->option_host_video ?? 1;
        $moduleinstance->option_participants_video = $moduleinstance->option_participants_video ?? 1;
        $moduleinstance->option_audio = $moduleinstance->option_audio ?? 3;
        $moduleinstance->option_mute_upon_entry = $moduleinstance->option_mute_upon_entry ?? 1;
        $moduleinstance->option_auto_recording = $moduleinstance->option_auto_recording ?? 0;
        $moduleinstance->meetingpassword = $moduleinstance->meetingpassword ?? '';
        // Ricorrenza (salva tutto come già hai in precedenza)
        $moduleinstance->meeting_type = $moduleinstance->meeting_type ?? 2;
        $moduleinstance->recurrence_type = $moduleinstance->recurrence_type ?? null;
        $moduleinstance->repeat_interval = $moduleinstance->repeat_interval ?? null;
        $moduleinstance->weekly_days = $moduleinstance->weekly_days ?? null;
        $moduleinstance->monthly_day = $moduleinstance->monthly_day ?? null;
        $moduleinstance->monthly_week = $moduleinstance->monthly_week ?? null;
        $moduleinstance->monthly_week_day = $moduleinstance->monthly_week_day ?? null;
        $moduleinstance->end_times = $moduleinstance->end_times ?? null;
        $moduleinstance->end_date_time = $moduleinstance->end_date_time ?? null;
    } catch (Exception $e) {
        throw new moodle_exception('failedtocreatemeeting', 'mod_zoomsdk', '', null, $e->getMessage());
    }
    $id = $DB->insert_record('zoomsdk', $moduleinstance);
    $moduleinstance->id = $id;
    return $id;
}

function zoomsdk_update_instance(stdClass $moduleinstance, $mform = null): bool {
    global $DB;
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    $moduleinstance->option_waiting_room = $moduleinstance->option_waiting_room ?? 0;
    $moduleinstance->option_jbh = $moduleinstance->option_jbh ?? 0;
    $moduleinstance->option_authenticated_users = $moduleinstance->option_authenticated_users ?? 0;
    $moduleinstance->option_host_video = $moduleinstance->option_host_video ?? 1;
    $moduleinstance->option_participants_video = $moduleinstance->option_participants_video ?? 1;
    $moduleinstance->option_audio = $moduleinstance->option_audio ?? 3;
    $moduleinstance->option_mute_upon_entry = $moduleinstance->option_mute_upon_entry ?? 1;
    $moduleinstance->option_auto_recording = $moduleinstance->option_auto_recording ?? 0;
    $moduleinstance->meetingpassword = $moduleinstance->meetingpassword ?? '';
    return $DB->update_record('zoomsdk', $moduleinstance);
}

function zoomsdk_delete_instance(int $id): bool {
    global $DB;
    $zoomsdk = $DB->get_record('zoomsdk', ['id' => $id]);
    if (!$zoomsdk) return false;
    zoomsdk_delete_zoom_meeting($zoomsdk->meetingid);
    $DB->delete_records('zoomsdk_attendance', ['zoomsdkid' => $id]);
    $DB->delete_records('zoomsdk', ['id' => $id]);
    return true;
}