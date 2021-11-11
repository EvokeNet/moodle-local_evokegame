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
        $badgeutil = new badge();

        $hasbadges = $badgeutil->get_course_badges($this->course->id);
        $badges = [];
        if ($hasbadges) {
            $badges = $badgeutil->get_course_badges_with_user_award($this->user->id, $this->course->id);
        }

        $points = new point($this->course->id, $this->user->id);

        $userutil = new user();

        $skillutil = new skill();
        $skills = $skillutil->get_course_skills_set($this->course->id, $this->user->id);

        return [
            'contextid' => $this->context->id,
            'points' => $points->mypoints->points,
            'userfirstname' => $this->user->firstname,
            'useravatar' => $userutil->get_user_avatar_or_image($this->user),
            'hasskills' => $skills != false,
            'skills' => $skills,
            'courseid' => $this->course->id,
            'hasbadges' => $hasbadges,
            'badges' => $badges
        ];
    }
}
