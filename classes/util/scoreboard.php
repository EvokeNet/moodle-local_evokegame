<?php

namespace local_evokegame\util;

class scoreboard {
    public function get_scoreboard($courseid) {
        global $DB;

        $sql = 'SELECT u.*, p.points
                FROM {evokegame_points} p
                INNER JOIN {user} u ON u.id = p.userid
                WHERE p.courseid = :courseid
                ORDER BY p.id ASC';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }
}