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
 * Generates JWT signature for Zoom SDK.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();

$zoomsdkid = required_param('zoomsdkid', PARAM_INT);

// Get records.
$zoomsdk = $DB->get_record('zoomsdk', ['id' => $zoomsdkid], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $zoomsdk->course], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('zoomsdk', $zoomsdk->id, $course->id, false, MUST_EXIST);

// Check permissions.
$context = context_module::instance($cm->id);
require_capability('mod/zoomsdk:view', $context);

// Get SDK credentials.
$sdkkey = get_config('mod_zoomsdk', 'sdk_key');
$sdksecret = get_config('mod_zoomsdk', 'sdk_secret');

if (empty($sdkkey) || empty($sdksecret)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'SDK credentials not configured']);
    die();
}

// Determine role.
$ishost = has_capability('mod/zoomsdk:addinstance', $context);
$role = $ishost ? 1 : 0;

// Generate JWT.
$iat = time();
$exp = $iat + 7200; // 2 hours.

$payload = [
    'sdkKey' => $sdkkey,
    'mn' => $zoomsdk->meetingid,
    'role' => $role,
    'iat' => $iat,
    'exp' => $exp,
    'tokenExp' => $exp,
];

/**
 * Base64 URL encode.
 *
 * @param string $data Data to encode.
 * @return string Encoded string.
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
$payloadjson = json_encode($payload);

$base64header = base64url_encode($header);
$base64payload = base64url_encode($payloadjson);

$signatureinput = $base64header . '.' . $base64payload;
$signature = base64url_encode(hash_hmac('sha256', $signatureinput, $sdksecret, true));

$jwt = $signatureinput . '.' . $signature;

// Track attendance.
$attendance = new stdClass();
$attendance->zoomsdkid = $zoomsdk->id;
$attendance->userid = $USER->id;
$attendance->jointime = time();
$attendance->leavetime = null;
$attendance->duration = null;

$DB->insert_record('zoomsdk_attendance', $attendance);

// Return response.
header('Content-Type: application/json');
echo json_encode([
    'signature' => $jwt,
    'sdkKey' => $sdkkey,
    'meetingNumber' => $zoomsdk->meetingid,
    'userName' => fullname($USER),
    'userEmail' => $USER->email,
    'passWord' => $zoomsdk->password,
    'role' => $role,
    'leaveUrl' => (new moodle_url('/mod/zoomsdk/view.php', ['id' => $cm->id]))->out(false),
]);
