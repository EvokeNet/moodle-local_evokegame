<?php

/**
 * This file contains the evokegame element courseaccess's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_courseaccess;

use local_evokegame\util\skill;

defined('MOODLE_INTERNAL') || die();

/**
 * The evokegame element courseaccess's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_evokegame\badgecriteria {
    public function user_achieved_criteria(): bool {
        $totalaccessdays = $this->count_course_access_days();

        $requireddays = (int) $this->badgecriteria->value;
        if ($totalaccessdays >= $requireddays) {
            return true;
        }

        return false;
    }

    public function get_user_criteria_progress(): bool {
        return 0;
    }

    private function count_course_access_days() {
        global $DB;

        // TODO: adicionar context para poder diminuir escopo da query e aumentar performance
        $sql = 'SELECT
                    id,
                    DATE(FROM_UNIXTIME(timecreated)) as date
                FROM {logstore_standard_log}
                WHERE userid = :userid AND courseid = :courseid AND target = :target
                GROUP BY date
                ORDER BY date';

        $records = $DB->get_records_sql($sql, ['userid' => $this->userid, 'courseid' => $this->badgecriteria->courseid, 'target' => 'course']);

        if (!$records) {
            return 0;
        }

        return count($records);
    }
}
