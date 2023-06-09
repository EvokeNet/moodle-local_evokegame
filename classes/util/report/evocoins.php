<?php

namespace local_evokegame\util\report;

class evocoins {
    public function get_course_total($courseid) {
        $activities = $this->get_course_activities_with($courseid);

        if (!$activities) {
            return 0;
        }

        return array_reduce($activities, function($carry, $item) {
            $carry += $item->value;

            return $carry;
        });
    }

    public function get_course_activities_with($courseid) {
        global $DB;

        $sql = 'SELECT ecm.id, ecm.cmid, ecm.value
                FROM {evokegame_evcs_modules} ecm
                INNER JOIN {course_modules} cm ON cm.id = ecm.cmid
                WHERE cm.course = :courseid';

        $activitieswithevocoins = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$activitieswithevocoins) {
            return false;
        }

        return array_values($activitieswithevocoins);
    }

    public function get_course_total_distributed($courseid) {
        global $DB;

        $sql = "SELECT SUM(coins) as total
                FROM {evokegame_evcs_transactions}
                WHERE courseid = :courseid AND action = 'in'";

        $record = $DB->get_record_sql($sql, ['courseid' => $courseid]);

        if (!$record) {
            return 0;
        }

        return $record->total;
    }
}
