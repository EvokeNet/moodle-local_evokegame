<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\util\badge;
use local_evokegame\util\point;
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
            $badges = $badgeutil->get_course_badges_with_user_award($USER->id, $this->course->id);
        }

        return [
            'courseid' => $this->course->id,
            'points' => $points->mypoints->points,
            'hasbadges' => $hasbadges,
            'badges' => $badges
        ];
    }
}
