<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\util\badge;
use local_evokegame\util\point;
use local_evokegame\util\user;
use mod_evokeportfolio\util\group;
use mod_evokeportfolio\util\section as sectionutil;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class dashboard implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        global $USER;

        $points = new point($this->course->id, $USER->id);

        $badgeutil = new badge();

        $hasbadges = $badgeutil->get_course_badges($this->course->id);
        $badges = [];
        if ($hasbadges) {
            $hasbadges = true;
            $badges = $badgeutil->get_course_badges_with_user_award($USER->id, $this->course->id, $this->context->id);
        }

        $userutil = new user();

        return [
            'userfirstname' => $USER->firstname,
            'useravatar' => $userutil->get_user_avatar_or_image($USER),
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'points' => $points->mypoints->points,
            'hasbadges' => $hasbadges,
            'badges' => $badges
        ];
    }
}
