<?php

/**
 * This file contains the evokegame element skillpoints's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_skillpoints;

use local_evokegame\util\skill;
use local_evokegame\util\skilluser;

defined('MOODLE_INTERNAL') || die();

/**
 * The evokegame element skillpoints's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_evokegame\badgecriteria {
    protected $targetname = '';

    public function user_achieved_criteria(): bool {
        $skillutil = new skilluser($this->userid);

        if (!$skillpoints = $skillutil->get_total_skill_points($this->badgecriteria->target)) {
            return false;
        }

        return $skillpoints >= ((int) $this->badgecriteria->value);
    }

    public function get_user_criteria_progress(): int {
        $skillutil = new skill();

        $usercourseskills = $skillutil->get_course_skills_set($this->badgecriteria->courseid, $this->userid);

        if (!$usercourseskills) {
            return 0;
        }

        $this->targetname = $this->badgecriteria->target;

        foreach ($usercourseskills as $usercourseskill) {
            if ($usercourseskill['id'] == $this->badgecriteria->target) {
                $this->targetname = $usercourseskill['skill'];

                if ($usercourseskill['userpoints'] == 0) {
                    return 0;
                }

                if ($usercourseskill['userpoints'] >= $this->badgecriteria->value) {
                    return 100;
                }

                return (int)($usercourseskill['userpoints'] * 100 / $this->badgecriteria->value);
            }
        }

        return 0;
    }

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'evokegamebadgecriteria_skillpoints');

        $progress = $this->get_user_criteria_progress();

        $langdata = new \stdClass();
        $langdata->name = $this->targetname;
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
