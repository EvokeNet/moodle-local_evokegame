<?php

/**
 * Evoke badge skill aggregation criteria class
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\badgecriteria\skillaggregation;

defined('MOODLE_INTERNAL') || die;

use local_evokegame\badgecriteria\base;
use local_evokegame\util\skill;

class badgecriteria extends base {
    public function check_if_user_achieved_criteria($userid, $badgecriteria) {
        $skillutil = new skill();

        $usercourseskills = $skillutil->get_course_skills_set($badgecriteria->courseid, $userid);

        if (!$usercourseskills) {
            return false;
        }

        $criteriaskills = explode(',', $badgecriteria->target);
        $totalpoints = 0;
        foreach ($usercourseskills as $usercourseskill) {
            foreach ($criteriaskills as $skill) {
                if ($usercourseskill['skill'] == $skill) {
                    $totalpoints += $usercourseskill['points'];
                }
            }
        }

        if ($totalpoints >= $badgecriteria->value) {
            return true;
        }

        return false;
    }
}
