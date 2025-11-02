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

    public function get_user_criteria_progress(): int {
        $totalaccessdays = $this->count_course_access_days();

        if ($totalaccessdays == 0) {
            return 0;
        }

        $requireddays = (int) $this->badgecriteria->value;
        if ($totalaccessdays >= $requireddays) {
            return 100;
        }

        return (int)($totalaccessdays * 100 / $this->badgecriteria->value);
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

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'evokegamebadgecriteria_courseaccess');

        $progress = $this->get_user_criteria_progress();

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'evokegamebadgecriteria_courseaccess', $this->badgecriteria->value);

        return '<p class="mb-0">'.$pluginname.'
                        <a class="btn btn-link p-0"
                           role="button"
                           data-container="body"
                           data-toggle="popover"
                           data-placement="right"
                           data-html="true"
                           tabindex="0"
                           data-trigger="focus"
                           data-content="<div class=\'no-overflow\'><p>'.$criteriaprogresdesc.'</p></div>">
                            <i class="icon fa fa-info-circle text-info fa-fw " title="'.$pluginname.'" role="img" aria-label="'.$pluginname.'"></i>
                        </a>
                    </p>
                    <div class="progress ml-0">
                        <div class="progress-bar" role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100">'.$progress.'%</div>
                    </div>';
    }
}
