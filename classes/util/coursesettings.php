<?php

namespace local_evokegame\util;

class coursesettings {
    public function process_form($formdata) {
        $data = (array) $formdata;

        $courseid = $data['courseid'];

        unset($data['courseid']);

        foreach ($data as $key => $value) {
            $settingkey = $key . '-' . $courseid;

            set_config($settingkey, $value, 'local_evokegame');
        }
    }
}