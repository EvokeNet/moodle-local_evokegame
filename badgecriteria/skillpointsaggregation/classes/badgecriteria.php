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
                if (strtolower($usercourseskill['skill']) == $skill) {
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

    public function get_user_criteria_progress_by_skill(): array {
        $skillutil = new skill();

        $usercourseskills = $skillutil->get_course_skills_set($this->badgecriteria->courseid, $this->userid, true);

        if (!$usercourseskills) {
            return [];
        }

        $criteriaskills = explode(',', $this->badgecriteria->target);

        $skills = [];
        foreach ($usercourseskills as $usercourseskill) {
            foreach ($criteriaskills as $skill) {
                if (strtolower($usercourseskill['skill']) == $skill) {
                    $skills[$skill] = $usercourseskill['points'];
                }
            }
        }

        if (!$skills) {
            return [];
        }

        foreach ($skills as $skill => $points) {
            $skills[$skill] = (int)($points * 100 / $this->badgecriteria->value);
        }

        return $skills;
    }

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'evokegamebadgecriteria_skillpointsaggregation');

        $skillsprogress = $this->get_user_criteria_progress_by_skill();

        if (!$skillsprogress) {
            return '';
        }

        $langdata = new \stdClass();
        $langdata->name = $this->badgecriteria->target;
        $langdata->value = $this->badgecriteria->value;

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'evokegamebadgecriteria_skillpointsaggregation', $langdata);

        $total = array_reduce($skillsprogress, function($carry, $item) {
            $carry += $item;

            return $carry;
        });

        if ($total >= 100) {
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
                        <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                    </div>';
        }

        $output = '<p class="mb-0">'.$pluginname.'
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
                    </p>';
        $output .= '<div class="progress ml-0">';

        $i = 0;
        foreach ($skillsprogress as $skill => $progress) {
            $progressbg = $this->get_bg_class($i);

            $output .= '<div class="progress-bar '.$progressbg.'" role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100">'.$skill.'</div>';

            $i++;
        }

        $output .= '</div>';

        return $output;
    }

    private function get_bg_class($id) {
        $data = [
            'bg-success',
            'bg-info',
            'bg-warning',
            'bg-success',
            'bg-info',
            'bg-warning',
            'bg-success',
            'bg-info',
            'bg-warning',
            'bg-success'
        ];

        return $data[$id];
    }
}
