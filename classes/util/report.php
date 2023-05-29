<?php

/**
 * Report util class
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

use mod_portfoliobuilder\util\user;

defined('MOODLE_INTERNAL') || die;

class report {
    public function get_course_total_evocoins($courseid) {
        $activities = $this->get_course_activities_with_evocoins($courseid);

        if (!$activities) {
            return 0;
        }

        return array_reduce($activities, function($carry, $item) {
            $carry += $item->value;

            return $carry;
        });
    }

    public function get_course_activities_with_evocoins($courseid) {
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

    public function get_course_total_students($context, $courseid) {
        global $DB;

        $sql = 'SELECT DISTINCT u.id, u.firstname, u.lastname, u.email';

        $capjoin = get_enrolled_with_capabilities_join($context, '', 'mod/portfoliobuilder:submit');

        $sql .= ' FROM {user} u ' . $capjoin->joins;

        $sql .= ' WHERE 1 = 1 AND ' . $capjoin->wheres;

        $params = $capjoin->params;

        $records = $DB->get_records_sql($sql, $params);

        if (!$records) {
            return false;
        }

        return count($records);
    }

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

    public function get_course_total_distributed_evocoins($courseid) {
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
