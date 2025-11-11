<?php
// ... (license header as before) ...

class mod_zoomsdk_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        // ... tutte le definizioni esistenti ...
        $mform->setType('end_times', PARAM_INT);
        // --- ELEMENTI STANDARD ---
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
    public function get_data() {
        $data = parent::get_data();
        // Normalizzazione end_times: se array prendi valore singolo o vuoto
        if ($data && is_array($data->end_times)) {
            $vals = array_filter($data->end_times, function($v) { return $v!=='' && $v!==null; });
            $data->end_times = empty($vals) ? 0 : (int)array_shift($vals);
        } else if ($data && !is_null($data->end_times)) {
            $data->end_times = (int)$data->end_times;
        }
        // Forza meeting_type se recurring o impostazioni analoghe
        if ($data && empty($data->meeting_type)) {
            $data->meeting_type = (!empty($data->recurring)) ? 3 : 2; // fallback sensato
        }
        return $data;
    }
}
