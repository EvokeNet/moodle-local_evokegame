<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\util\badge;
use local_evokegame\util\point;
use local_evokegame\util\skill;
use local_evokegame\util\user;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class profile implements renderable, templatable {
    protected $course;
    protected $context;
    protected $user;

    public function __construct($context, $course, $user) {
        $this->course = $course;
        $this->context = $context;
        $this->user = $user;
    }

    public function export_for_template(renderer_base $output) {
        global $USER;

        $badgeutil = new badge();

        $achievements = $badgeutil->get_awarded_course_achievements($this->user->id, $this->course->id, $this->context->id);

        $hasachievements = false;
        if ($achievements) {
            $hasachievements = true;
        }

        $points = new point($this->course->id, $this->user->id);

        $userutil = new user();

        $skillutil = new skill();
        $skills = $skillutil->get_course_skills_set($this->course->id, $this->user->id);

        $itsme = $USER->id == $this->user->id;

        $badges = $badgeutil->get_user_course_badges_with_criterias($this->user->id, $this->course->id, $this->context->id, 1);

        return [
            'coursename' => $this->course->fullname,
            'contextid' => $this->context->id,
            'points' => (int) $points->mypoints->points,
            'id' => $this->user->id,
            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,
            'email' => $this->user->email,
            'useravatar' => $userutil->get_user_avatar_or_image($this->user),
            'hasskills' => $skills != false,
            'skills' => $skills,
            'courseid' => $this->course->id,
            'hasachievements' => $hasachievements,
            'achievements' => $achievements,
            'itsme' => $itsme,
            'hasbadges' => !empty($badges),
            'badges' => $badges
        ];
    }
}
