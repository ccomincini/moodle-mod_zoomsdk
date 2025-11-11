<?php
require_once(__DIR__ . '/../../config.php');

require_login();

$zoomsdkid = required_param('zoomsdkid', PARAM_INT);

$zoomsdk = $DB->get_record('zoomsdk', ['id' => $zoomsdkid], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $zoomsdk->course], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('zoomsdk', $zoomsdk->id, $course->id, false, MUST_EXIST);

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

// Generate signature for Component View.
$iat = time();
$exp = $iat + 7200;

$payload = [
    'sdkKey' => $sdkkey,
    'mn' => $zoomsdk->meetingid,
    'role' => $role,
    'iat' => $iat,
    'exp' => $exp,
    'tokenExp' => $exp,
];

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

// Build Component View URL.
$username = urlencode(fullname($USER));
$useremail = urlencode($USER->email);
$password = urlencode($zoomsdk->password);
$leaveurl = urlencode((new moodle_url('/mod/zoomsdk/view.php', ['id' => $cm->id]))->out(false));

$componenturl = "https://zoom.us/wc/join/{$zoomsdk->meetingid}?" . http_build_query([
    'name' => $username,
    'email' => $useremail,
    'pwd' => $password,
    'signature' => $jwt,
    'sdkKey' => $sdkkey,
    'role' => $role,
    'lang' => 'it-IT',
    'leaveUrl' => $leaveurl,
]);

// Track attendance.
$attendance = new stdClass();
$attendance->zoomsdkid = $zoomsdk->id;
$attendance->userid = $USER->id;
$attendance->jointime = time();
$attendance->leavetime = null;
$attendance->duration = null;

$DB->insert_record('zoomsdk_attendance', $attendance);

header('Content-Type: application/json');
echo json_encode(['componentUrl' => $componenturl]);
