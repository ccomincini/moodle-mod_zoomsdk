<?php
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'zoomsdk');
$zoomsdk = $DB->get_record('zoomsdk', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/zoomsdk:view', $context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$event = \mod_zoomsdk\event\course_module_viewed::create([
    'objectid' => $zoomsdk->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('zoomsdk', $zoomsdk);
$event->add_record_snapshot('course_modules', $cm);
$event->trigger();

$PAGE->set_url('/mod/zoomsdk/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($zoomsdk->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Carica Zoom SDK 4.1.0 da CDN
$PAGE->requires->js(new moodle_url('https://source.zoom.us/4.1.0/lib/vendor/react.min.js'), true);
$PAGE->requires->js(new moodle_url('https://source.zoom.us/4.1.0/lib/vendor/react-dom.min.js'), true);
$PAGE->requires->js(new moodle_url('https://source.zoom.us/4.1.0/lib/vendor/redux.min.js'), true);
$PAGE->requires->js(new moodle_url('https://source.zoom.us/4.1.0/lib/vendor/redux-thunk.min.js'), true);
$PAGE->requires->js(new moodle_url('https://source.zoom.us/4.1.0/lib/vendor/lodash.min.js'), true);
$PAGE->requires->js(new moodle_url('https://source.zoom.us/zoom-meeting-4.1.0.min.js'), true);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($zoomsdk->name));

echo $OUTPUT->box_start('generalbox');
echo html_writer::tag('p', html_writer::tag('strong', get_string('starttime', 'mod_zoomsdk') . ': ') .
    userdate($zoomsdk->starttime));
echo html_writer::tag('p', html_writer::tag('strong', get_string('duration', 'mod_zoomsdk') . ': ') .
    format_time($zoomsdk->duration));
echo html_writer::tag('p', html_writer::tag('strong', get_string('meetingid', 'mod_zoomsdk') . ': ') .
    $zoomsdk->meetingid);
echo $OUTPUT->box_end();

echo html_writer::start_div('', ['style' => 'margin-top: 20px;']);

echo html_writer::tag('button', 'Partecipa alla Riunione', [
    'id' => 'join-meeting-btn',
    'class' => 'btn btn-primary btn-lg',
    'style' => 'width: 100%; padding: 15px; font-size: 18px;',
]);

echo html_writer::tag('div', '', [
    'id' => 'zmmtg-root',
    'style' => 'display: none; width: 100%; height: 700px; margin-top: 20px;',
]);

echo html_writer::end_div();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('join-meeting-btn').addEventListener('click', function() {
        this.disabled = true;
        this.textContent = 'Connessione in corso...';
        
        fetch(M.cfg.wwwroot + '/mod/zoomsdk/generate_signature.php?zoomsdkid=<?php echo $zoomsdk->id; ?>')
            .then(r => r.json())
            .then(data => {
                console.log('Signature data:', data);
                initZoomSDK(data);
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Errore: ' + err.message);
                this.disabled = false;
                this.textContent = 'Riprova';
            });
    });
});

function initZoomSDK(config) {
    console.log('Initializing Zoom SDK...');
    
    document.getElementById('join-meeting-btn').style.display = 'none';
    document.getElementById('zmmtg-root').style.display = 'block';
    
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareWebSDK();
    
    ZoomMtg.init({
        leaveUrl: config.leaveUrl,
        disableInvite: true,
        disableRecord: true,
        success: function() {
            console.log('SDK initialized, joining meeting...');
            
            ZoomMtg.join({
                signature: config.signature,
                sdkKey: config.sdkKey,
                meetingNumber: config.meetingNumber,
                userName: config.userName,
                userEmail: config.userEmail,
                passWord: config.passWord,
                success: function(res) {
                    console.log('✅ Joined successfully', res);
                },
                error: function(error) {
                    console.error('❌ Join error:', error);
                    alert('Errore join: ' + error.errorMessage);
                }
            });
        },
        error: function(error) {
            console.error('❌ SDK init error:', error);
            alert('Errore SDK: ' + error.errorMessage);
        }
    });
}
</script>

<?php
echo $OUTPUT->footer();