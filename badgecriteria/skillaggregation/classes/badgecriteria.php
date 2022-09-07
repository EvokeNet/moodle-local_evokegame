<?php

/**
 * This file contains the evokegame element skillaggregation's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_skillaggregation;

use local_evokegame\util\skill;

defined('MOODLE_INTERNAL') || die();

/**
 * The evokegame element skillaggregation's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \evokegamebadgecriteria\badgecriteria {
    public function user_achieved_criteria(): bool {
        $skillutil = new skill();

        $usercourseskills = $skillutil->get_course_skills_set($this->badgecriteria->courseid, $this->userid);

        if (!$usercourseskills) {
            return false;
        }

        $criteriaskills = explode(',', $this->badgecriteria->target);
        $totalpoints = 0;
        foreach ($usercourseskills as $usercourseskill) {
            foreach ($criteriaskills as $skill) {
                if ($usercourseskill['skill'] == $skill) {
                    $totalpoints += $usercourseskill['points'];
                }
            }
        }

        if ($totalpoints >= $this->badgecriteria->value) {
            return true;
        }

        return false;
    }

    public function get_user_criteria_progress(): bool {
        return 0;
    }
}
