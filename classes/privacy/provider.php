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
 * Privacy Subsystem implementation for mod_zoomsdk.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_zoomsdk\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for mod_zoomsdk.
 *
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns metadata about this plugin.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'zoomsdk_attendance',
            [
                'userid' => 'privacy:metadata:zoomsdk_attendance:userid',
                'jointime' => 'privacy:metadata:zoomsdk_attendance:jointime',
                'leavetime' => 'privacy:metadata:zoomsdk_attendance:leavetime',
                'duration' => 'privacy:metadata:zoomsdk_attendance:duration',
            ],
            'privacy:metadata:zoomsdk_attendance'
        );

        $collection->add_external_location_link(
            'zoom',
            [
                'fullname' => 'privacy:metadata:zoom:fullname',
                'email' => 'privacy:metadata:zoom:email',
            ],
            'privacy:metadata:zoom'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * @param int $userid The user ID.
     * @return contextlist The list of contexts.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {zoomsdk} z ON z.id = cm.instance
            INNER JOIN {zoomsdk_attendance} za ON za.zoomsdkid = z.id
                 WHERE za.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'zoomsdk',
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a context.
     *
     * @param userlist $userlist The userlist.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT za.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {zoomsdk} z ON z.id = cm.instance
                  JOIN {zoomsdk_attendance} za ON za.zoomsdkid = z.id
                 WHERE cm.id = :cmid";

        $params = [
            'modname' => 'zoomsdk',
            'cmid' => $context->instanceid,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts list.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $cm = get_coursemodule_from_id('zoomsdk', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $zoomsdk = $DB->get_record('zoomsdk', ['id' => $cm->instance]);
            if (!$zoomsdk) {
                continue;
            }

            $attendance = $DB->get_records('zoomsdk_attendance', [
                'zoomsdkid' => $zoomsdk->id,
                'userid' => $user->id,
            ]);

            if (!empty($attendance)) {
                $data = [];
                foreach ($attendance as $record) {
                    $data[] = (object) [
                        'jointime' => \core_privacy\local\request\transform::datetime($record->jointime),
                        'leavetime' => $record->leavetime ? \core_privacy\local\request\transform::datetime($record->leavetime) : '-',
                        'duration' => $record->duration ? format_time($record->duration) : '-',
                    ];
                }

                writer::with_context($context)->export_data(
                    [get_string('privacy:metadata:zoomsdk_attendance', 'mod_zoomsdk')],
                    (object) ['attendance' => $data]
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('zoomsdk', $context->instanceid);
        if (!$cm) {
            return;
        }

        $DB->delete_records('zoomsdk_attendance', ['zoomsdkid' => $cm->instance]);
    }

    /**
     * Delete all user data for the specified user in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts list.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $cm = get_coursemodule_from_id('zoomsdk', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $DB->delete_records('zoomsdk_attendance', [
                'zoomsdkid' => $cm->instance,
                'userid' => $userid,
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('zoomsdk', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();

        foreach ($userids as $userid) {
            $DB->delete_records('zoomsdk_attendance', [
                'zoomsdkid' => $cm->instance,
                'userid' => $userid,
            ]);
        }
    }
}
