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
            if (strtolower($usercourseskill['skill']) == $this->badgecriteria->target) {
                if ($usercourseskill['points'] >= $this->badgecriteria->value) {
                    return true;
                }

                break;
            }
        }

        return false;
    }

    public function get_user_criteria_progress(): int {
        $skillutil = new skill();

        $usercourseskills = $skillutil->get_course_skills_set($this->badgecriteria->courseid, $this->userid);

        if (!$usercourseskills) {
            return 0;
        }

        foreach ($usercourseskills as $usercourseskill) {
            if (strtolower($usercourseskill['skill']) == $this->badgecriteria->target) {
                if ($usercourseskill['points'] == 0) {
                    return 0;
                }

                if ($usercourseskill['points'] >= $this->badgecriteria->value) {
                    return 100;
                }

                return (int)($usercourseskill['points'] * 100 / $this->badgecriteria->value);
            }
        }

        return 0;
    }

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'evokegamebadgecriteria_skillpoints');

        $progress = $this->get_user_criteria_progress();

        $langdata = new \stdClass();
        $langdata->name = $this->badgecriteria->target;
        $langdata->value = $this->badgecriteria->value;

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'evokegamebadgecriteria_skillpoints', $langdata);

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
