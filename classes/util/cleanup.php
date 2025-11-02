<?php

namespace local_evokegame\util;

class cleanup {
    public function delete_activity_skills($cmid) {
        global $DB;

        $skillsmodules = $DB->delete_records('evokegame_skills_modules', ['cmid' => $cmid]);

        if (!$skillsmodules) {
            return true;
        }

        foreach ($skillsmodules as $skillsmodule) {
            $DB->delete_records('evokegame_skills_users', ['skillmoduleid' => $skillsmodule->id]);
        }

        return $DB->delete_records('evokegame_skills_modules', ['cmid' => $cmid]);
    }

    public function delete_activity_coins($cmid) {
        global $DB;

        return $DB->delete_records('evokegame_evcs_modules', ['cmid' => $cmid]);
    }

    public function delete_course_skills($courseid) {
        global $DB;

        $skills = $DB->get_records('evokegame_skills', ['courseid' => $courseid]);

        if (!$skills) {
            return;
        }

        foreach ($skills as $skill) {
            $skillsmodules = $DB->get_records('evokegame_skills_modules', ['skillid' => $skill->id]);

            if (!$skillsmodules) {
                continue;
            }

            foreach ($skillsmodules as $skillsmodule) {
                $DB->delete_records('evokegame_skills_users', ['skillmoduleid' => $skillsmodule->id]);
            }

            $DB->delete_records('evokegame_skills_modules', ['skillid' => $skill->id]);
        }

        $DB->delete_records('evokegame_skills', ['courseid' => $courseid]);
    }

    public function delete_course_coins($courseid) {
        global $DB;

        $sql = 'SELECT evcs.id FROM {evokegame_evcs_modules} evcs
                INNER JOIN {course_modules} cm ON cm.id = evcs.cmid AND cm.course = :courseid';

        $evcsmodules = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$evcsmodules) {
            return true;
        }

        foreach ($evcsmodules as $evcsmodule) {
            $DB->delete_records('evokegame_evcs_modules', ['id' => $evcsmodule->id]);
        }

        return true;
    }

    public function delete_course_badges($courseid) {
        global $DB;

        $badges = $DB->get_records('evokegame_badges', ['courseid' => $courseid]);

        if (!$badges) {
            return true;
        }

        foreach ($badges as $badge) {
            $DB->delete_records('evokegame_badges_criterias', ['evokebadgeid' => $badge->id]);
        }

        $DB->delete_records('evokegame_badges', ['courseid' => $courseid]);

        return true;
    }
}
