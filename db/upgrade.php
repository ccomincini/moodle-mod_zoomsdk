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
 * Upgrade script for mod_zoomsdk.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the zoomsdk module.
 *
 * @param int $oldversion The old version of the module.
 * @return bool
 */
function xmldb_zoomsdk_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025111102) {
        // Define fields to be added to zoomsdk table.
        $table = new xmldb_table('zoomsdk');

        // Add meeting_type field.
        $field = new xmldb_field('meeting_type', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2', 'host_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Make start_time nullable for type 8 meetings.
        $field = new xmldb_field('start_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'meeting_type');
        $dbman->change_field_notnull($table, $field);

        // Add recurrence_type field.
        $field = new xmldb_field('recurrence_type', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'join_url');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add repeat_interval field.
        $field = new xmldb_field('repeat_interval', XMLDB_TYPE_INTEGER, '3', null, null, null, null, 'recurrence_type');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add weekly_days field.
        $field = new xmldb_field('weekly_days', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'repeat_interval');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add monthly_day field.
        $field = new xmldb_field('monthly_day', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'weekly_days');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add monthly_week field.
        $field = new xmldb_field('monthly_week', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'monthly_day');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add monthly_week_day field.
        $field = new xmldb_field('monthly_week_day', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'monthly_week');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add end_times field.
        $field = new xmldb_field('end_times', XMLDB_TYPE_INTEGER, '3', null, null, null, null, 'monthly_week_day');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add end_date_time field.
        $field = new xmldb_field('end_date_time', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'end_times');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add index on meeting_type.
        $index = new xmldb_index('meeting_type', XMLDB_INDEX_NOTUNIQUE, ['meeting_type']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_mod_savepoint(true, 2025111102, 'zoomsdk');
    }

    return true;
}
