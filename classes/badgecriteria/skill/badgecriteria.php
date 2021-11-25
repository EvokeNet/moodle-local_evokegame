<?php

/**
 * Evoke badge skill criteria class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\badgecriteria\skill;

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

        foreach ($usercourseskills as $usercourseskill) {
            if ($usercourseskill['skill'] == $badgecriteria->target) {
                if ($usercourseskill['points'] >= $badgecriteria->value) {
                    return true;
                }

                break;
            }
        }

        return false;
    }
}
