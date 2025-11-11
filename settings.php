<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'mod_zoomsdk/sdk_key',
        get_string('sdkkey', 'mod_zoomsdk'),
        get_string('sdkkey_desc', 'mod_zoomsdk'),
        '',
        PARAM_TEXT
    ));
    
    $settings->add(new admin_setting_configpasswordunmask(
        'mod_zoomsdk/sdk_secret',
        get_string('sdksecret', 'mod_zoomsdk'),
        get_string('sdksecret_desc', 'mod_zoomsdk'),
        ''
    ));
}
