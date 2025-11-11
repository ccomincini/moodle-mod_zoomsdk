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
 * Config form for Zoom SDK Meeting (calls moodleform_mod!)
 * @package    mod_zoomsdk
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_zoomsdk_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        // ... (tutto il contenuto precedente) ...
        $mform->setType('end_times', PARAM_INT);
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
    public function get_data() {
        $data = parent::get_data();
        if ($data && is_array($data->end_times)) {
            $vals = array_filter($data->end_times, function($v) { return $v!=='' && $v!==null; });
            $data->end_times = empty($vals) ? 0 : (int)array_shift($vals);
        } else if ($data && !is_null($data->end_times)) {
            $data->end_times = (int)$data->end_times;
        }
        if ($data && empty($data->meeting_type)) {
            $data->meeting_type = (!empty($data->recurring)) ? 3 : 2;
        }
        return $data;
    }
}
