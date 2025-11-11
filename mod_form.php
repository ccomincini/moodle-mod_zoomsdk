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
 * The main configuration form.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_zoomsdk_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // General.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('meetingname', 'mod_zoomsdk'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Meeting settings.
        $mform->addElement('header', 'meetingsettings', get_string('meetingsettings', 'mod_zoomsdk'));

        // Meeting type selection.
        $meetingtypes = [
            1 => get_string('type_instant', 'mod_zoomsdk'),
            2 => get_string('type_scheduled', 'mod_zoomsdk'),
            3 => get_string('type_recurring_fixed', 'mod_zoomsdk'),
            8 => get_string('type_recurring_nofixed', 'mod_zoomsdk'),
        ];
        $mform->addElement('select', 'meeting_type', get_string('meetingtype', 'mod_zoomsdk'), $meetingtypes);
        $mform->addHelpButton('meeting_type', 'meetingtype', 'mod_zoomsdk');
        $mform->setDefault('meeting_type', 2);

        // Start time (hidden for instant, optional for recurring no-fixed).
        $mform->addElement('date_time_selector', 'start_time', get_string('starttime', 'mod_zoomsdk'));
        $mform->setDefault('start_time', time() + 3600);
        $mform->hideIf('start_time', 'meeting_type', 'eq', 1);

        // Duration.
        $mform->addElement('duration', 'duration', get_string('duration', 'mod_zoomsdk'), ['optional' => false]);
        $mform->setDefault('duration', 3600);

        // Recurrence settings header.
        $mform->addElement('header', 'recurrencesettings', get_string('recurrencesettings', 'mod_zoomsdk'));
        $mform->hideIf('recurrencesettings', 'meeting_type', 'noteq', 3);
        $mform->hideIf('recurrencesettings', 'meeting_type', 'noteq', 8);

        // Recurrence type.
        $recurrencetypes = [
            1 => get_string('recurrence_daily', 'mod_zoomsdk'),
            2 => get_string('recurrence_weekly', 'mod_zoomsdk'),
            3 => get_string('recurrence_monthly', 'mod_zoomsdk'),
        ];
        $mform->addElement('select', 'recurrence_type', get_string('recurrence_type', 'mod_zoomsdk'), $recurrencetypes);
        $mform->addHelpButton('recurrence_type', 'recurrence_type', 'mod_zoomsdk');
        $mform->hideIf('recurrence_type', 'meeting_type', 'noteq', 3);
        $mform->hideIf('recurrence_type', 'meeting_type', 'noteq', 8);

        // Repeat interval.
        $mform->addElement('text', 'repeat_interval', get_string('repeat_interval', 'mod_zoomsdk'), ['size' => '5']);
        $mform->setType('repeat_interval', PARAM_INT);
        $mform->setDefault('repeat_interval', 1);
        $mform->addHelpButton('repeat_interval', 'repeat_interval', 'mod_zoomsdk');
        $mform->hideIf('repeat_interval', 'meeting_type', 'noteq', 3);
        $mform->hideIf('repeat_interval', 'meeting_type', 'noteq', 8);

        // Weekly days (only for weekly recurrence).
        $weekdays = [
            1 => get_string('day_sunday', 'mod_zoomsdk'),
            2 => get_string('day_monday', 'mod_zoomsdk'),
            3 => get_string('day_tuesday', 'mod_zoomsdk'),
            4 => get_string('day_wednesday', 'mod_zoomsdk'),
            5 => get_string('day_thursday', 'mod_zoomsdk'),
            6 => get_string('day_friday', 'mod_zoomsdk'),
            7 => get_string('day_saturday', 'mod_zoomsdk'),
        ];
        $weekdaygroup = [];
        foreach ($weekdays as $value => $label) {
            $weekdaygroup[] = $mform->createElement('advcheckbox', 'weekly_days[' . $value . ']', '', $label, [], [0, $value]);
        }
        $mform->addGroup($weekdaygroup, 'weekly_days_group', get_string('weekly_days', 'mod_zoomsdk'), '<br/>', false);
        $mform->addHelpButton('weekly_days_group', 'weekly_days', 'mod_zoomsdk');
        $mform->hideIf('weekly_days_group', 'recurrence_type', 'noteq', 2);
        $mform->hideIf('weekly_days_group', 'meeting_type', 'noteq', 3);
        $mform->hideIf('weekly_days_group', 'meeting_type', 'noteq', 8);

        // Monthly day.
        $mform->addElement('text', 'monthly_day', get_string('monthly_day', 'mod_zoomsdk'), ['size' => '5']);
        $mform->setType('monthly_day', PARAM_INT);
        $mform->addHelpButton('monthly_day', 'monthly_day', 'mod_zoomsdk');
        $mform->hideIf('monthly_day', 'recurrence_type', 'noteq', 3);
        $mform->hideIf('monthly_day', 'meeting_type', 'noteq', 3);
        $mform->hideIf('monthly_day', 'meeting_type', 'noteq', 8);

        // Monthly week.
        $monthlyweeks = [
            -1 => get_string('week_last', 'mod_zoomsdk'),
            1 => get_string('week_first', 'mod_zoomsdk'),
            2 => get_string('week_second', 'mod_zoomsdk'),
            3 => get_string('week_third', 'mod_zoomsdk'),
            4 => get_string('week_fourth', 'mod_zoomsdk'),
        ];
        $mform->addElement('select', 'monthly_week', get_string('monthly_week', 'mod_zoomsdk'), $monthlyweeks);
        $mform->addHelpButton('monthly_week', 'monthly_week', 'mod_zoomsdk');
        $mform->hideIf('monthly_week', 'recurrence_type', 'noteq', 3);
        $mform->hideIf('monthly_week', 'meeting_type', 'noteq', 3);
        $mform->hideIf('monthly_week', 'meeting_type', 'noteq', 8);

        // Monthly week day.
        $mform->addElement('select', 'monthly_week_day', get_string('monthly_week_day', 'mod_zoomsdk'), $weekdays);
        $mform->addHelpButton('monthly_week_day', 'monthly_week_day', 'mod_zoomsdk');
        $mform->hideIf('monthly_week_day', 'recurrence_type', 'noteq', 3);
        $mform->hideIf('monthly_week_day', 'meeting_type', 'noteq', 3);
        $mform->hideIf('monthly_week_day', 'meeting_type', 'noteq', 8);

        // End times.
        $mform->addElement('text', 'end_times', get_string('end_times', 'mod_zoomsdk'), ['size' => '5']);
        $mform->setType('end_times', PARAM_INT);
        $mform->addHelpButton('end_times', 'end_times', 'mod_zoomsdk');
        $mform->hideIf('end_times', 'meeting_type', 'noteq', 3);
        $mform->hideIf('end_times', 'meeting_type', 'noteq', 8);

        // End date time.
        $mform->addElement('date_time_selector', 'end_date_time', get_string('end_date_time', 'mod_zoomsdk'), ['optional' => true]);
        $mform->addHelpButton('end_date_time', 'end_date_time', 'mod_zoomsdk');
        $mform->hideIf('end_date_time', 'meeting_type', 'noteq', 3);
        $mform->hideIf('end_date_time', 'meeting_type', 'noteq', 8);

        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
    }

    /**
     * Preprocess form data.
     *
     * @param array $defaultvalues Default values.
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        // Convert weekly_days string to array for checkboxes.
        if (!empty($defaultvalues['weekly_days'])) {
            $days = explode(',', $defaultvalues['weekly_days']);
            foreach ($days as $day) {
                $defaultvalues['weekly_days[' . $day . ']'] = $day;
            }
        }
    }

    /**
     * Enforce validation rules.
     *
     * @param array $data Array of submitted form data.
     * @param array $files Array of uploaded files.
     * @return array Array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate start time for scheduled and recurring with fixed time.
        if (in_array($data['meeting_type'], [2, 3])) {
            if (!empty($data['start_time']) && $data['start_time'] < time()) {
                $errors['start_time'] = get_string('starttime_past', 'mod_zoomsdk');
            }
        }

        // Validate duration.
        if ($data['duration'] < 60) {
            $errors['duration'] = get_string('duration_toolow', 'mod_zoomsdk');
        }

        // Validate recurring meeting settings.
        if (in_array($data['meeting_type'], [3, 8])) {
            // Validate repeat interval.
            if (!empty($data['repeat_interval']) && $data['repeat_interval'] < 1) {
                $errors['repeat_interval'] = get_string('err_repeat_interval', 'mod_zoomsdk');
            }

            // Validate weekly days for weekly recurrence.
            if ($data['recurrence_type'] == 2) {
                $selecteddays = false;
                if (!empty($data['weekly_days'])) {
                    foreach ($data['weekly_days'] as $day) {
                        if ($day > 0) {
                            $selecteddays = true;
                            break;
                        }
                    }
                }
                if (!$selecteddays) {
                    $errors['weekly_days_group'] = get_string('err_weekly_days', 'mod_zoomsdk');
                }
            }

            // Validate monthly day.
            if ($data['recurrence_type'] == 3 && !empty($data['monthly_day'])) {
                if ($data['monthly_day'] < 1 || $data['monthly_day'] > 31) {
                    $errors['monthly_day'] = get_string('err_monthly_day', 'mod_zoomsdk');
                }
            }

            // Validate end times.
            if (!empty($data['end_times'])) {
                if ($data['end_times'] < 1 || $data['end_times'] > 60) {
                    $errors['end_times'] = get_string('err_end_times', 'mod_zoomsdk');
                }
            }
        }

        return $errors;
    }

    /**
     * Process data before saving.
     *
     * @param stdClass $data Form data.
     * @return stdClass Modified data.
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data) {
            // Convert weekly_days array to comma-separated string.
            if (!empty($data->weekly_days)) {
                $selecteddays = [];
                foreach ($data->weekly_days as $day) {
                    if ($day > 0) {
                        $selecteddays[] = $day;
                    }
                }
                $data->weekly_days = !empty($selecteddays) ? implode(',', $selecteddays) : null;
            } else {
                $data->weekly_days = null;
            }

            // Set start_time to null for type 8 if not provided.
            if ($data->meeting_type == 8 && empty($data->start_time)) {
                $data->start_time = null;
            }
        }

        return $data;
    }
}
