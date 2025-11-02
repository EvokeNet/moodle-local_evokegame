<?php

namespace local_evokegame\util;

class coursesettings {
    public function process_form($formdata, $context) {
        $data = (array) $formdata;

        $courseid = $data['courseid'];

        unset($data['courseid']);

        foreach ($data as $key => $value) {
            if ($key == 'scoreboard_image' && isset($value)) {
                $draftitemid = file_get_submitted_draft_itemid('scoreboard_image');

                file_save_draft_area_files($draftitemid, $context->id, 'local_evokegame', 'scoreboard_image', $courseid, ['maxbytes' => 1, 'accepted_types' => 'optimised_image']);

                continue;
            }

            $settingkey = $key . '-' . $courseid;

            set_config($settingkey, $value, 'local_evokegame');
        }
    }
}