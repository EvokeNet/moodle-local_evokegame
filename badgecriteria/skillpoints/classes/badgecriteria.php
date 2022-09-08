<?php

/**
 * This file contains the evokegame element skillpoints's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_skillpoints;

use local_evokegame\util\skill;

defined('MOODLE_INTERNAL') || die();

/**
 * The evokegame element skillpoints's core interaction API.
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

        foreach ($usercourseskills as $usercourseskill) {
            if ($usercourseskill['skill'] == $this->badgecriteria->target) {
                if ($usercourseskill['points'] >= $this->badgecriteria->value) {
                    return true;
                }

                break;
            }
        }

        return false;
    }

    public function get_user_criteria_progress(): bool {
        return 0;
    }
}
