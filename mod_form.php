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

/*
 * Form di configurazione per Zoom SDK meeting, aggiornato per includere opzioni avanzate:
 * - Breakout Rooms (placeholder)
 * - Opzioni Sicurezza (password, waiting room, join before host, autenticazione)
 * - Opzioni Media (video, audio, mute, registrazione automatica)
 *
 * @package    mod_zoomsdk
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

        // --- BREAKOUT ROOMS --- (solo placeholder per ora)
        $mform->addElement('header', 'breakoutrooms', 'Breakout rooms');
        $mform->addElement('static', 'breakoutrooms_info', '', 'No rooms<br>Add a room by clicking');

        // --- SICUREZZA ---
        $mform->addElement('header', 'sicurezza', get_string('security', 'mod_zoomsdk'));

        // Password obbligatoria + campo password
        $mform->addElement('advcheckbox', 'requirepasscode', 'Password riunione obbligatoria');
        $mform->setDefault('requirepasscode', 1);
        $mform->addElement('text', 'meetingpassword', 'Imposta password', ['maxlength' => 10]);
        $mform->setType('meetingpassword', PARAM_TEXT);
        $mform->addRule('meetingpassword', 'Password obbligatoria', 'required', null, 'client');
        $mform->addHelpButton('meetingpassword', 'passwordhelp', 'mod_zoomsdk');

        // Waiting room
        $mform->addElement('advcheckbox', 'option_waiting_room', 'Abilita sala d’attesa');
        $mform->setDefault('option_waiting_room', 1);
        // Join before host
        $mform->addElement('advcheckbox', 'option_jbh', 'Consenti di partecipare alla riunione in qualsiasi momento');
        $mform->setDefault('option_jbh', 0);
        // Authentication required
        $mform->addElement('advcheckbox', 'option_authenticated_users', 'Richiedi autenticazione per partecipare');
        $mform->setDefault('option_authenticated_users', 0);

        // --- MEDIA ---
        $mform->addElement('header', 'media', 'Media');
        // Host video
        $mform->addGroup([
            $mform->createElement('radio', 'option_host_video', '', 'On', 1),
            $mform->createElement('radio', 'option_host_video', '', 'Off', 0),
        ], 'option_host_video_group', 'Avvia video dell’host', null, false);
        $mform->setDefault('option_host_video', 1);

        // Partecipant video
        $mform->addGroup([
            $mform->createElement('radio', 'option_participants_video', '', 'On', 1),
            $mform->createElement('radio', 'option_participants_video', '', 'Off', 0),
        ], 'option_participants_video_group', 'Video dei partecipanti', null, false);
        $mform->setDefault('option_participants_video', 1);

        // Audio options
        $mform->addGroup([
            $mform->createElement('radio', 'option_audio', '', 'Solo telefono', 1),
            $mform->createElement('radio', 'option_audio', '', 'Solo audio del computer', 2),
            $mform->createElement('radio', 'option_audio', '', 'Audio del computer e telefono', 3),
        ], 'option_audio_group', 'Opzioni audio', null, false);
        $mform->setDefault('option_audio', 3);

        // Muto all’accesso
        $mform->addElement('advcheckbox', 'option_mute_upon_entry', 'Microfono dei partecipanti muto all’accesso');
        $mform->setDefault('option_mute_upon_entry', 1);

        // Auto recording
        $record_opts = [0 => 'None', 1 => 'Locale', 2 => 'Cloud'];
        $mform->addElement('select', 'option_auto_recording', 'Automatic recording', $record_opts);
        $mform->setDefault('option_auto_recording', 0);

        // --- MEETING TIME & RECURRING ---
        $mform->addElement('header', 'schedule', get_string('schedule', 'mod_zoomsdk'));
        $mform->addElement('date_time_selector', 'start_time', get_string('starttime', 'mod_zoomsdk'));
        $mform->setDefault('start_time', time() + 3600);
        $mform->addElement('duration', 'duration', get_string('duration', 'mod_zoomsdk'), ['optional' => false]);
        $mform->setDefault('duration', 3600);

        // RICORRENZA - come mod_zoom
        $mform->addElement('advcheckbox', 'recurring', 'Meeting ricorrente');
        $recurrencetypes = [
            1 => 'Giornaliera',
            2 => 'Settimanale',
            3 => 'Mensile',
            8 => 'Senza orario fisso',
        ];
        $mform->addElement('select', 'recurrence_type', 'Tipo di ricorrenza', $recurrencetypes);
        $mform->hideIf('recurrence_type', 'recurring', 'notchecked');
        $options = [];
        for ($i=1;$i<=90;$i++) { $options[$i] = $i; }
        $mform->addElement('select', 'repeat_interval', 'Ripeti ogni', $options);
        $mform->hideIf('repeat_interval', 'recurrence_type', 'eq', 8);
        $mform->hideIf('repeat_interval', 'recurring', 'notchecked');
        // -- GIORNI SETTIMANA --
        $weekdays = [1=>'Dom',2=>'Lun',3=>'Mar',4=>'Mer',5=>'Gio',6=>'Ven',7=>'Sab'];
        $group = [];
        foreach ($weekdays as $k=>$day) {
            $group[] = $mform->createElement('advcheckbox', 'weekly_days['.$k.']', '', $day, [], [0, $k]);
        }
        $mform->addGroup($group, 'weekly_days_group', 'Ripeti il', '', false);
        $mform->hideIf('weekly_days_group', 'recurrence_type', 'noteq', 2);
        $mform->hideIf('weekly_days_group', 'recurring', 'notchecked');
        // -- MENSILE -- puoi aggiungere radio/select come mod_zoom se vuoi una UX avanzata --
        $mform->addElement('text', 'monthly_day', 'Giorno del mese', ['size'=>5]);
        $mform->setType('monthly_day', PARAM_INT);
        $mform->hideIf('monthly_day', 'recurrence_type', 'noteq', 3);
        $mform->hideIf('monthly_day', 'recurring', 'notchecked');
        // Fine ricorrenza
        $group = [];
        $group[] = $mform->createElement('radio', 'end_date_option', '', 'Termina entro il', 1);
        $group[] = $mform->createElement('date_selector', 'end_date_time', '');
        $group[] = $mform->createElement('radio', 'end_date_option', '', 'Dopo N occorrenze', 2);
        $group[] = $mform->createElement('text', 'end_times', '', ['size'=>3]);
        $group[] = $mform->createElement('static', 'end_times_text', '', 'occurrence');
        $mform->addGroup($group, 'endgroup', 'Fine ricorrenza', '', false);
        $mform->hideIf('endgroup', 'recurrence_type', 'eq', 8);
        $mform->hideIf('endgroup', 'recurring', 'notchecked');

        // --- ELEMENTI STANDARD ---
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
