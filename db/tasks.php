<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'mod_zoomsdk\task\cleanup_deleted',
        'blocking' => 0,
        'minute' => '*/15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
