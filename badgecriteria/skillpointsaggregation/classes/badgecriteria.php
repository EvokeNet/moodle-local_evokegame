<?php

/**
 * This file contains the evokegame element skillpointsaggregation's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_skillpointsaggregation;

use local_evokegame\util\skill;

defined('MOODLE_INTERNAL') || die();

/**
 * The evokegame element skillpointsaggregation's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_evokegame\badgecriteria {
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

    public function get_user_criteria_progress(): int {
        $skillutil = new skill();

        $usercourseskills = $skillutil->get_course_skills_set($this->badgecriteria->courseid, $this->userid);

        if (!$usercourseskills) {
            return 0;
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

        if ($totalpoints == 0) {
            return 0;
        }

        if ($totalpoints >= $this->badgecriteria->value) {
            return 100;
        }

        return (int)($totalpoints * 100 / $this->badgecriteria->value);
    }
}
