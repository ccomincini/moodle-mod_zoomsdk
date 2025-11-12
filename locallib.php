<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Internal library of functions for module zoomsdk.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Crea un meeting Zoom tramite API
 */
function zoomsdk_create_zoom_meeting(stdClass $data, string $hostuserid): stdClass {
    global $CFG;
    
    require_once($CFG->dirroot . '/mod/zoomsdk/classes/webservice.php');
    
    // Determina il tipo di meeting
    $meetingtype = 2; // Default: scheduled
    if (!empty($data->recurring)) {
        if (!empty($data->recurrence_type) && $data->recurrence_type == 8) {
            $meetingtype = 8; // Recurring no fixed time
        } else {
            $meetingtype = 3; // Recurring with fixed time
        }
    }
    
    // Costruisci payload base
    $meetingdata = [
        'topic' => $data->name,
        'type' => $meetingtype,
    ];
    
    // Duration è obbligatoria SOLO per type 2 e 3, NON per type 8
    if (in_array($meetingtype, [2, 3])) {
        $meetingdata['duration'] = (int)ceil($data->duration / 60);
    }
    
    // Start_time è obbligatorio SOLO per type 2 e 3, NON per type 8
    if (in_array($meetingtype, [2, 3]) && !empty($data->start_time)) {
        $meetingdata['start_time'] = gmdate('Y-m-d\TH:i:s\Z', $data->start_time);
    }
    
    // Aggiungi password se presente
    if (!empty($data->meetingpassword)) {
        $meetingdata['password'] = $data->meetingpassword;
    }
    
    // SETTINGS - costruisci solo con valori validi
    $settings = [];
    
    // Host video
    if (isset($data->option_host_video)) {
        $settings['host_video'] = (bool)$data->option_host_video;
    }
    
    // Participant video
    if (isset($data->option_participants_video)) {
        $settings['participant_video'] = (bool)$data->option_participants_video;
    }
    
    // Join before host (solo se waiting_room è disabilitata)
    if (!empty($data->option_jbh) && empty($data->option_waiting_room)) {
        $settings['join_before_host'] = true;
    } else {
        $settings['join_before_host'] = false;
    }
    
    // Waiting room (solo se join_before_host è disabilitato)
    if (!empty($data->option_waiting_room) && empty($data->option_jbh)) {
        $settings['waiting_room'] = true;
    } else {
        $settings['waiting_room'] = false;
    }
    
    // Meeting authentication
    if (!empty($data->option_authenticated_users)) {
        $settings['meeting_authentication'] = true;
    }
    
    // Mute upon entry
    if (isset($data->option_mute_upon_entry)) {
        $settings['mute_upon_entry'] = (bool)$data->option_mute_upon_entry;
    }
    
    // Audio options
    if (!empty($data->option_audio)) {
        switch ($data->option_audio) {
            case 1:
                $settings['audio'] = 'telephony';
                break;
            case 2:
                $settings['audio'] = 'voip';
                break;
            case 3:
                $settings['audio'] = 'both';
                break;
            default:
                $settings['audio'] = 'both';
        }
    }
    
    // Auto recording
    if (!empty($data->option_auto_recording)) {
        switch ($data->option_auto_recording) {
            case 1:
                $settings['auto_recording'] = 'local';
                break;
            case 2:
                $settings['auto_recording'] = 'cloud';
                break;
            default:
                $settings['auto_recording'] = 'none';
        }
    }
    
    $meetingdata['settings'] = $settings;
    
    // RECURRENCE - SOLO per type 3 (recurring with fixed time)
    // NON inviare MAI recurrence per type 8!
    if ($meetingtype == 3 && !empty($data->recurrence_type) && $data->recurrence_type != 8) {
        $recurrence = [
            'type' => (int)$data->recurrence_type,
            'repeat_interval' => !empty($data->repeat_interval) ? (int)$data->repeat_interval : 1,
        ];
        
        // Weekly days (solo per recurrence_type = 2)
        if ($data->recurrence_type == 2) {
            if (!empty($data->weekly_days)) {
                if (is_array($data->weekly_days)) {
                    $days = [];
                    foreach ($data->weekly_days as $day => $value) {
                        if (!empty($value)) {
                            $days[] = $day;
                        }
                    }
                    if (!empty($days)) {
                        $recurrence['weekly_days'] = implode(',', $days);
                    } else {
                        $recurrence['weekly_days'] = (string)((int)date('w') + 1);
                    }
                } else {
                    $recurrence['weekly_days'] = $data->weekly_days;
                }
            } else {
                $recurrence['weekly_days'] = (string)((int)date('w') + 1);
            }
        }
        
        // Monthly day (solo per recurrence_type = 3)
        if ($data->recurrence_type == 3) {
            $recurrence['monthly_day'] = !empty($data->monthly_day) ? (int)$data->monthly_day : (int)date('j');
        }
        
        // End times o End date
        if (!empty($data->end_times) && $data->end_times > 0) {
            $recurrence['end_times'] = min((int)$data->end_times, 60);
        } else if (!empty($data->end_date_time)) {
            $recurrence['end_date_time'] = gmdate('Y-m-d\TH:i:s\Z', $data->end_date_time);
        } else {
            $recurrence['end_times'] = 10;
        }
        
        $meetingdata['recurrence'] = $recurrence;
    }
    
    // Usa il nostro webservice indipendente
    try {
        $service = new \mod_zoomsdk\webservice();
        $response = $service->make_call("users/{$hostuserid}/meetings", $meetingdata, 'post');
        
        if (empty($response->id)) {
            throw new moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 
                'Meeting ID vuoto nella risposta API');
        }
        
        return $response;
        
    } catch (Exception $e) {
        throw new moodle_exception('apicallfailed', 'mod_zoomsdk', '', null, 
            'Zoom: ' . $e->getMessage());
    }
}

/**
 * Ottieni utente Zoom da email
 */
    function zoomsdk_get_zoom_user(string $email) {
        try {
                    // DEBUG DETTAGLIATO
        echo "<pre style='background: lightblue; padding: 20px; border: 3px solid blue;'>";
        echo "=== DEBUG GET_USER ZOOM ===\n";
        echo "Email cercata: " . htmlspecialchars($email) . "\n";
        
        // Verifica config
        $sdk_key = get_config('mod_zoomsdk', 'sdk_key');
        $sdk_secret = get_config('mod_zoomsdk', 'sdk_secret');
        echo "SDK Key configurata: " . (empty($sdk_key) ? 'NO!' : 'Sì (' . substr($sdk_key, 0, 15) . '...)') . "\n";
        echo "SDK Secret configurata: " . (empty($sdk_secret) ? 'NO!' : 'Sì (nascosto)') . "\n\n";
        
        try {
            echo "Creando webservice...\n";
            $service = new \mod_zoomsdk\webservice();
            echo "Webservice creato OK!\n";
            echo "Chiamo API Zoom con email: " . htmlspecialchars($email) . "\n\n";

            $service = new \mod_zoomsdk\webservice();
            $user = $service->get_user($email);
                        
            echo "<strong style='color: green;'>SUCCESS! User trovato:</strong>\n";
            echo "ID: " . $user->id . "\n";
            echo "Email: " . $user->email . "\n";
            echo "</pre>\n";
            
            return $user;
            
            
            // Log successo per debug
            error_log('=== ZOOM USER TROVATO ===');
            error_log('Email: ' . $email);
            error_log('User ID: ' . ($user->id ?? 'N/A'));
            error_log('=========================');
            
            return $user;
            
        } catch (Exception $e) {
            // Log errore dettagliato
                        
            echo "<pre style='background: red; color: white; padding: 20px; border: 3px solid black;'>";
            echo "=== ERRORE GET_USER ZOOM ===\n";
            echo "Email cercata: " . htmlspecialchars($email) . "\n";
            echo "Tipo Exception: " . get_class($se) . "\n";
            echo "Messaggio errore: " . htmlspecialchars($se->getMessage()) . "\n";
            echo "File: " . $se->getFile() . "\n";
            echo "Line: " . $se->getLine() . "\n";
            echo "============================\n";
            echo "</pre>\n";
            die();
            
            error_log('=== ERRORE GET_USER ZOOM ===');
            error_log('Email cercata: ' . $email);
            error_log('Errore: ' . $e->getMessage());
            error_log('============================');
            
            return false;
        }
    }

/**
 * Elimina meeting Zoom
 */
function zoomsdk_delete_zoom_meeting(string $meetingid): void {
    try {
        $service = new \mod_zoomsdk\webservice();
        $service->delete_meeting($meetingid);
    } catch (Exception $e) {
        error_log('Errore delete_meeting Zoom: ' . $e->getMessage());
    }
}
