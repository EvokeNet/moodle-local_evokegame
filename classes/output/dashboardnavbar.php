<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\util\badge;
use local_evokegame\util\evocoin;
use local_evokegame\util\point;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class dashboardnavbar implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        global $USER;

        $evcs = new evocoin($USER->id);

        $badgeutil = new badge();

        $hasbadges = $badgeutil->get_course_badges($this->course->id);
        $badges = [];
        if ($hasbadges) {
            $hasbadges = true;
            $badges = $badgeutil->get_course_badges_with_user_award($USER->id, $this->course->id, $this->context->id);
        }

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'evcs' => (int) $evcs->get_coins(),
            'hasbadges' => $hasbadges,
            'badges' => $badges
        ];
    }
}
