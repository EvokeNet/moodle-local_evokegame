<?php

namespace local_evokegame\temp_migration;

class migration {
    public function migrate_courses($type = 'evocoins') {
        global $DB;

//        $courses = $DB->get_records_sql('SELECT * FROM {course} WHERE id > 1');
        $courses = $DB->get_records_sql('SELECT * FROM {course} WHERE id = 42');

        foreach ($courses as $course) {
            $coursemoduleswithskills = $this->get_coursemodules_customfields($course);

            if ($type == 'evocoins') {
                $this->migrate_evocoins($coursemoduleswithskills);
            }

            if ($type == 'submission') {
                $this->migrate_submission_skills($coursemoduleswithskills);

//                if ($submissionskills) {
//                    $this->insert_evokegame_evcs_modules($submissionskills);
//                }
            }
        }
    }

    private function migrate_submission_skills($coursemoduleswithskills) {
        $submissionskills = $this->extract_submission_skills($coursemoduleswithskills);
    }

    public function extract_submission_skills($coursemoduleswithskills) {
        $data = [];

        foreach ($coursemoduleswithskills as $key => $coursemodule) {
            if (array_key_exists('evocoins', $coursemodule)) {
                $data[$key] = $coursemodule['evocoins'];
            }
        }

        return $data;
    }

    private function migrate_evocoins($coursemoduleswithskills) {
        $onlyevocoins = $this->extract_evocoins_fields($coursemoduleswithskills);

        if ($onlyevocoins) {
            $this->insert_evokegame_evcs_modules($onlyevocoins);
        }
    }

    private function extract_evocoins_fields($coursemoduleswithskills) {
        $data = [];

        foreach ($coursemoduleswithskills as $key => $coursemodule) {
            if (array_key_exists('evocoins', $coursemodule)) {
                $data[$key] = $coursemodule['evocoins'];
            }
        }

        return $data;
    }

    private function insert_evokegame_evcs_modules($onlyevocoins) {
        global $DB;

        $data = [];

        foreach ($onlyevocoins as $key => $onlyevocoin) {
            $data[] = [
                'cmid' => $key,
                'value' => $onlyevocoin,
                'timecreated' => time(),
                'timemodified' => time(),
            ];
        }

        $DB->insert_records('evokegame_evcs_modules', $data);
    }

    private function get_coursemodules_customfields($course) {
        global $CFG;

        $modinfo = get_fast_modinfo($course);

        $coursemoduleswithsskills = [];
        foreach ($modinfo->get_cms() as $coursemodule) {
            $handler = \local_evokegame\customfield\mod_handler::create();

            $data = $handler->export_instance_data_object($coursemodule->id);

            $customfields = (array)$data;

            if (!$customfields) {
                continue;
            }

            $skills = $this->get_customfields_in_use_by_coursemodule($customfields);

            if (!$skills) {
                continue;
            }

            $coursemoduleswithsskills[$coursemodule->id] = $skills;
        }

        return $coursemoduleswithsskills;
    }

    private function get_customfields_in_use_by_coursemodule($customfields) {
        $data = [];
        foreach ($customfields as $key => $customfield) {
            if ($customfield == '0' || $customfield == 0) {
                continue;
            }

            $data[$key] = $customfield;
        }

        return $data;
    }
}
