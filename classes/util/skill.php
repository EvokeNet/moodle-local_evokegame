<?php

/**
 * Skill util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

class skill {
    public function get_course_skills_set($courseid, $userid = null) {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = 'SELECT id, skill, sum(points) as points
                FROM {evokegame_logs}
                WHERE courseid = :courseid AND userid = :userid
                GROUP BY skill';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $points = (int) $record->points * 10;
            if ($points > 100) {
                $points = 100;
            }

            $data[] = [
                'skill' => ucfirst($record->skill),
                'points' => $points
            ];
        }

        return $data;
    }
}
