<?php
namespace mod_zoomsdk\task;

defined('MOODLE_INTERNAL') || die();

class cleanup_deleted extends \core\task\scheduled_task {
    
    public function get_name() {
        return get_string('task_cleanup_deleted', 'mod_zoomsdk');
    }
    
    public function execute() {
        global $DB;
        
        mtrace('Starting zoomsdk cleanup of deleted modules...');
        
        // Trova course_modules con deletioninprogress
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'zoomsdk']);
        
        $sql = "SELECT cm.instance 
                FROM {course_modules} cm
                WHERE cm.module = :moduleid 
                AND cm.deletioninprogress = 1";
        
        $instances = $DB->get_fieldset_sql($sql, ['moduleid' => $moduleid]);
        
        if (empty($instances)) {
            mtrace('No deleted modules to clean up.');
            return;
        }
        
        mtrace('Found ' . count($instances) . ' modules to delete.');
        
        foreach ($instances as $instanceid) {
            // Chiama la funzione di cancellazione
            require_once(__DIR__ . '/../../lib.php');
            
            if (zoomsdk_delete_instance($instanceid)) {
                mtrace("Deleted zoomsdk instance ID: $instanceid");
                
                // Rimuovi anche course_module
                $DB->delete_records('course_modules', [
                    'instance' => $instanceid,
                    'module' => $moduleid
                ]);
            } else {
                mtrace("Failed to delete zoomsdk instance ID: $instanceid");
            }
        }
        
        mtrace('Cleanup completed.');
    }
}
