<?php

/**
 * Evoke badge course access criteria class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\badgecriteria\courseaccess;

defined('MOODLE_INTERNAL') || die;

use local_evokegame\badgecriteria\base;

class badgecriteria extends base {
    public function check_if_user_achieved_criteria($userid, $badgecriteria) {
        $totalaccessdays = $this->count_course_access_days($userid, $badgecriteria->courseid);

        $requireddays = (int) $badgecriteria->value;
        if ($totalaccessdays >= $requireddays) {
            return true;
        }

        return false;
    }

    private function count_course_access_days($userid, $courseid) {
        global $DB;

        $sql = 'SELECT
                    id,
                    DATE(FROM_UNIXTIME(timecreated)) as date
                FROM {logstore_standard_log}
                WHERE userid = :userid AND courseid = :courseid AND target = :target
                GROUP BY date
                ORDER BY date';

        $records = $DB->get_records_sql($sql, ['userid' => $userid, 'courseid' => $courseid, 'target' => 'course']);

        if (!$records) {
            return 0;
        }

        return count($records);
    }
}
