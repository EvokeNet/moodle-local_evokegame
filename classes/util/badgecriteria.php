<?php

/**
 * Evoke badge criterias util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

use local_evokegame\badgecriteria\skill\badgecriteria as skillcriteria;
use local_evokegame\badgecriteria\courseaccess\badgecriteria as courseaccesscriteria;
use local_evokegame\badgecriteria\skillaggregation\badgecriteria as skillaggregationcriteria;

class badgecriteria {
    public const CRITERIA_SKILL_POINTS = 1;
    public const CRITERIA_COURSE_ACCESS = 2;
    public const CRITERIA_SKILL_POINTS_AGGREGATION = 3;

    public function get_evoke_badge_criterias($evokebadgeid) {
        global $DB;

        $records = $DB->get_records('evokegame_badges_criterias', ['evokebadgeid' => $evokebadgeid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }

    public function get_criteria_method_name($badgecriteriaid) {
        switch ($badgecriteriaid) {
            case 1:
                return 'skillpoints';
            case 2:
                return 'courseaccess';
            case 3:
                return 'skillpointsaggregation';
            default:
                return '';
        }
    }
}
