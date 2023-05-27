<?php

namespace local_evokegame\temp_migration;

class migration {
    public function migrate_courses($type = 'evocoins') {
        global $DB;

        if ($type == 'skillsusers') {
            $skillssubmission = new skills_users();

            $skillssubmission->migrate_skills_users();

            return;
        }

        if ($type == 'badgescriterias') {
            $skillssubmission = new badges_criterias();

            $skillssubmission->migrate_criterias();
        }

        $courses = $DB->get_records_sql('SELECT * FROM {course} WHERE id > 1');

        foreach ($courses as $course) {
            $coursemoduleswithskills = $this->get_coursemodules_customfields($course);

            if ($type == 'evocoins') {
                $evocoins = new evocoins($coursemoduleswithskills);

                $evocoins->migrate_evocoins();
            }

            if ($type == 'skills') {
                $skillssubmission = new skills_submission($course, $coursemoduleswithskills);

                $skillssubmission->migrate_skills();
            }
        }
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
