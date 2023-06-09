<?php

namespace local_evokegame\util\report;

class skills {
    public function get_course_skills_with_totalpoints($courseid) {
        global $DB;

        $sql = 'SELECT sm.skillid as id, s.name, SUM(sm.value) as value
                FROM {evokegame_skills_modules} sm
                INNER JOIN {course_modules} cm ON cm.id = sm.cmid
                INNER JOIN {evokegame_skills} s ON s.id = sm.skillid
                WHERE cm.course = :courseid
                GROUP BY sm.skillid';

        $skills = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$skills) {
            return false;
        }

        return array_values($skills);
    }
}
