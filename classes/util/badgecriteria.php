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

class badgecriteria {
    public function get_evoke_badge_criterias($evokebadgeid) {
        global $DB;

        $records = $DB->get_records('evokegame_badges_criterias', ['evokebadgeid' => $evokebadgeid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }

    public function get_evoke_badge_criterias_with_skill_name($badge) {
        global $DB;

        $criterias = $this->get_evoke_badge_criterias($badge->id);

        if (!$criterias) {
            return false;
        }

        $courseskills = $DB->get_records('evokegame_skills', ['courseid' => $badge->courseid]);

        foreach ($criterias as $criteria) {
            if ($criteria->method == 'skillpoints') {
                $criteria->target = $courseskills[$criteria->target]->name;
            }

            if ($criteria->method == 'skillpointsaggregation') {
                $skills = explode(',', $criteria->target);

                $skillnames = array_map(function($skillid) use ($courseskills) {
                    return $courseskills[$skillid]->name;
                }, $skills);

                $criteria->target = implode(', ', $skillnames);
            }
        }

        return array_values($criterias);
    }
}
