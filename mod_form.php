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
 * Configuration form for Zoom SDK Meeting
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_zoomsdk_mod_form extends moodleform_mod {
    
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        // --- GENERAL ---
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('meetingname', 'mod_zoomsdk'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        // --- BREAKOUT ROOMS (placeholder) ---
        $mform->addElement('header', 'breakoutrooms', get_string('breakoutrooms', 'mod_zoomsdk'));
        $mform->addElement('static', 'breakoutrooms_info', '', get_string('breakoutrooms_info', 'mod_zoomsdk'));

        // --- SICUREZZA ---
        $mform->addElement('header', 'sicurezza', get_string('security', 'mod_zoomsdk'));
        
        $mform->addElement('advcheckbox', 'requirepasscode', get_string('requirepasscode', 'mod_zoomsdk'));
        $mform->setDefault('requirepasscode', 1);
        
        // Password auto-generata
        $autopass = $this->generate_auto_password();
        $mform->addElement('text', 'meetingpassword', get_string('meetingpassword', 'mod_zoomsdk'), ['maxlength' => 10]);
        $mform->setType('meetingpassword', PARAM_TEXT);
        $mform->setDefault('meetingpassword', $autopass);
        $mform->addRule('meetingpassword', get_string('err_password_required', 'mod_zoomsdk'), 'required', null, 'client');
        $mform->addHelpButton('meetingpassword', 'passwordhelp', 'mod_zoomsdk');
        
        // Radio button: sala d'attesa vs join before host (mutualmente esclusivi)
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'access_option', '', get_string('option_waiting_room', 'mod_zoomsdk'), 'waiting_room');
        $radioarray[] = $mform->createElement('radio', 'access_option', '', get_string('option_jbh', 'mod_zoomsdk'), 'join_before_host');
        $mform->addGroup($radioarray, 'access_option_group', 'Modalità accesso partecipanti', '<br>', false);
        $mform->setDefault('access_option', 'waiting_room');
        
        $mform->addElement('advcheckbox', 'option_authenticated_users', get_string('option_authenticated_users', 'mod_zoomsdk'));
        $mform->setDefault('option_authenticated_users', 0);

        // --- MEDIA ---
        $mform->addElement('header', 'media', get_string('media', 'mod_zoomsdk'));
        
        $mform->addGroup([
            $mform->createElement('radio', 'option_host_video', '', 'On', 1),
            $mform->createElement('radio', 'option_host_video', '', 'Off', 0),
        ], 'option_host_video_group', get_string('option_host_video', 'mod_zoomsdk'), null, false);
        $mform->setDefault('option_host_video', 1);

        $mform->addGroup([
            $mform->createElement('radio', 'option_participants_video', '', 'On', 1),
            $mform->createElement('radio', 'option_participants_video', '', 'Off', 0),
        ], 'option_participants_video_group', get_string('option_participants_video', 'mod_zoomsdk'), null, false);
        $mform->setDefault('option_participants_video', 1);

        $mform->addGroup([
            $mform->createElement('radio', 'option_audio', '', 'Solo telefono', 1),
            $mform->createElement('radio', 'option_audio', '', 'Solo audio del computer', 2),
            $mform->createElement('radio', 'option_audio', '', 'Audio del computer e telefono', 3),
        ], 'option_audio_group', get_string('option_audio', 'mod_zoomsdk'), null, false);
        $mform->setDefault('option_audio', 3);

        $mform->addElement('advcheckbox', 'option_mute_upon_entry', get_string('option_mute_upon_entry', 'mod_zoomsdk'));
        $mform->setDefault('option_mute_upon_entry', 1);

        $record_opts = [0 => 'None', 1 => 'Locale', 2 => 'Cloud'];
        $mform->addElement('select', 'option_auto_recording', get_string('option_auto_recording', 'mod_zoomsdk'), $record_opts);
        $mform->setDefault('option_auto_recording', 0);

        // --- SCHEDULE/RICORRENZA ---
        $mform->addElement('header', 'schedule', get_string('schedule', 'mod_zoomsdk'));
        
        $mform->addElement('date_time_selector', 'start_time', get_string('starttime', 'mod_zoomsdk'));
        $mform->setDefault('start_time', time() + 120); // +2 minuti
        
        $mform->addElement('duration', 'duration', get_string('duration', 'mod_zoomsdk'), ['optional' => false]);
        $mform->setDefault('duration', 3600);

        // Ricorrenza
        $mform->addElement('advcheckbox', 'recurring', get_string('recurring', 'mod_zoomsdk'));
        
        $recurrencetypes = [
            1 => 'Giornaliera',
            2 => 'Settimanale',
            3 => 'Mensile',
            8 => 'Senza orario fisso',
        ];
        $mform->addElement('select', 'recurrence_type', get_string('recurrence_type', 'mod_zoomsdk'), $recurrencetypes);
        $mform->hideIf('recurrence_type', 'recurring', 'notchecked');
        
        $options = [];
        for ($i = 1; $i <= 90; $i++) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'repeat_interval', get_string('repeat_interval', 'mod_zoomsdk'), $options);
        $mform->hideIf('repeat_interval', 'recurrence_type', 'eq', 8);
        $mform->hideIf('repeat_interval', 'recurring', 'notchecked');

        // Giorni settimana
        $weekdays = [1 => 'Dom', 2 => 'Lun', 3 => 'Mar', 4 => 'Mer', 5 => 'Gio', 6 => 'Ven', 7 => 'Sab'];
        $group = [];
        foreach ($weekdays as $k => $day) {
            $group[] = $mform->createElement('advcheckbox', 'weekly_days[' . $k . ']', '', $day, [], [0, $k]);
        }
        $mform->addGroup($group, 'weekly_days_group', get_string('weekly_days_group', 'mod_zoomsdk'), '', false);
        $mform->hideIf('weekly_days_group', 'recurrence_type', 'noteq', 2);
        $mform->hideIf('weekly_days_group', 'recurring', 'notchecked');

        // Giorno mensile
        $mform->addElement('text', 'monthly_day', get_string('monthly_day', 'mod_zoomsdk'), ['size' => 5]);
        $mform->setType('monthly_day', PARAM_INT);
        $mform->hideIf('monthly_day', 'recurrence_type', 'noteq', 3);
        $mform->hideIf('monthly_day', 'recurring', 'notchecked');

        // Fine ricorrenza
        $group = [];
        $group[] = $mform->createElement('radio', 'end_date_option', '', get_string('end_date_time', 'mod_zoomsdk'), 1);
        $group[] = $mform->createElement('date_selector', 'end_date_time', '');
        $group[] = $mform->createElement('radio', 'end_date_option', '', get_string('end_times', 'mod_zoomsdk'), 2);
        $group[] = $mform->createElement('text', 'end_times', '', ['size' => 3]);
        $group[] = $mform->createElement('static', 'end_times_text', '', get_string('end_times_text', 'mod_zoomsdk'));
        $mform->addGroup($group, 'endgroup', get_string('end_date_option', 'mod_zoomsdk'), '', false);
        $mform->hideIf('endgroup', 'recurrence_type', 'eq', 8);
        $mform->hideIf('endgroup', 'recurring', 'notchecked');
        $mform->setType('end_times', PARAM_INT);

        // --- ELEMENTI STANDARD ---
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Genera password automatica (8 caratteri alfanumerici)
     */
    private function generate_auto_password() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public function get_data() {
        $data = parent::get_data();
        
        if ($data) {
            // Converti access_option in option_waiting_room e option_jbh
            if (isset($data->access_option)) {
                if ($data->access_option === 'waiting_room') {
                    $data->option_waiting_room = 1;
                    $data->option_jbh = 0;
                } else {
                    $data->option_waiting_room = 0;
                    $data->option_jbh = 1;
                }
            }
            
            // Normalizzazione end_times
            if (isset($data->end_times) && is_array($data->end_times)) {
                $vals = array_filter($data->end_times, function($v) { 
                    return $v !== '' && $v !== null; 
                });
                $data->end_times = empty($vals) ? 0 : (int)array_shift($vals);
            } else if (isset($data->end_times) && !is_null($data->end_times)) {
                $data->end_times = (int)$data->end_times;
            }
            
            // Forza meeting_type basandosi sul tipo di ricorrenza
            if (empty($data->meeting_type)) {
                if (!empty($data->recurring)) {
                    if (!empty($data->recurrence_type) && $data->recurrence_type == 8) {
                        $data->meeting_type = 8; // Ricorrente senza orario fisso
                    } else {
                        $data->meeting_type = 3; // Ricorrente con orario fisso
                    }
                } else {
                    $data->meeting_type = 2; // Meeting programmato normale
                }
            }
        }
        
        return $data;
    }
}
