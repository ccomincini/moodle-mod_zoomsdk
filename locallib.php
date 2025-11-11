<?php
// Internal library of functions for module zoomsdk.
// Aggiornato per trasmettere tutte le opzioni sicurezza/media a Zoom SDK

defined('MOODLE_INTERNAL') || die();

function zoomsdk_create_zoom_meeting(stdClass $data, string $hostuserid): stdClass {
    global $CFG;
    require_once($CFG->dirroot . '/mod/zoom/classes/webservice.php');
    $service = new \mod_zoom\webservice();
    $meetingdata = [
        'topic' => $data->name,
        'type' => $data->meeting_type ?? 2,
        'duration' => (int)ceil($data->duration / 60),
        'settings' => [
            // Sicurezza
            'host_video' => !empty($data->option_host_video),
            'participant_video' => !empty($data->option_participants_video),
            'join_before_host' => !empty($data->option_jbh),
            'waiting_room' => !empty($data->option_waiting_room),
            'meeting_authentication' => !empty($data->option_authenticated_users),
            'mute_upon_entry' => !empty($data->option_mute_upon_entry),
            // Audio
            'audio' => ($data->option_audio == 1) ? 'telephony' : ($data->option_audio == 2 ? 'voip' : 'both'),
            // Registrazione automatica
            'auto_recording' => ($data->option_auto_recording == 1) ? 'local' : ($data->option_auto_recording == 2 ? 'cloud' : 'none'),
            // Password (se necessaria)
            'password' => $data->meetingpassword ?? '',
        ],
    ];
    // Data/orario inizio (se non no fixed time)
    if (in_array($data->meeting_type, [2,3]) && !empty($data->start_time)) {
        $meetingdata['start_time'] = gmdate('Y-m-d\TH:i:s\Z', $data->start_time);
    }
    // Ricorrenza
    if (in_array($data->meeting_type, [3,8])) {
        $recurrence = [
            'type' => $data->recurrence_type ?? 1,
        ];
        if (!empty($data->repeat_interval)) $recurrence['repeat_interval'] = (int)$data->repeat_interval;
        if ($data->recurrence_type == 2 && !empty($data->weekly_days)) $recurrence['weekly_days'] = $data->weekly_days;
        if ($data->recurrence_type == 3 && !empty($data->monthly_day)) $recurrence['monthly_day'] = (int)$data->monthly_day;
        if ($data->recurrence_type == 3 && !empty($data->monthly_week)) $recurrence['monthly_week'] = (int)$data->monthly_week;
        if ($data->recurrence_type == 3 && !empty($data->monthly_week_day)) $recurrence['monthly_week_day'] = (int)$data->monthly_week_day;
        if (!empty($data->end_times)) $recurrence['end_times'] = (int)$data->end_times;
        else if (!empty($data->end_date_time)) $recurrence['end_date_time'] = gmdate('Y-m-d\TH:i:s\Z', $data->end_date_time);
        $meetingdata['recurrence'] = $recurrence;
    }
    $url = "users/{$hostuserid}/meetings";
    try {
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('make_call');
        $method->setAccessible(true);
        $response = $method->invoke($service, $url, $meetingdata, 'post');
        if (empty($response->id)) {
            throw new moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 'Meeting ID is empty in API response');
        }
        return $response;
    } catch (Exception $e) {
        throw new moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, $e->getMessage());
    }
}

function zoomsdk_get_zoom_user(string $email) {
    global $CFG;
    if (!file_exists($CFG->dirroot . '/mod/zoom/classes/webservice.php')) return false;
    require_once($CFG->dirroot . '/mod/zoom/classes/webservice.php');
    try {
        $service = new \mod_zoom\webservice();
        return $service->get_user($email);
    } catch (Exception $e) { return false; }
}

function zoomsdk_delete_zoom_meeting(string $meetingid): void {
    global $CFG;
    if (!file_exists($CFG->dirroot . '/mod/zoom/classes/webservice.php')) return;
    require_once($CFG->dirroot . '/mod/zoom/classes/webservice.php');
    try {
        $service = new \mod_zoom\webservice();
        $service->delete_meeting($meetingid, false);
    } catch (Exception $e) {}
}
