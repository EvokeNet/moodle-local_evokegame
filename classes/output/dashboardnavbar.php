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

        $isgameenabledincourse = get_config('local_evokegame', 'isgameenabledincourse-' . $this->course->id);
        $isgameenabled = false;
        if (is_null($isgameenabledincourse) || $isgameenabledincourse == 1) {
            $isgameenabled = true;
        }

        if (!$isgameenabled || !isloggedin() || isguestuser() || $this->course->id == 1) {
            return [
                'showblock' => false
            ];
        }

        $evcs = new evocoin($USER->id);

        $badgeutil = new badge();

        $hasbadges = $badgeutil->get_course_highlight_badges_with_user_award($USER->id, $this->course->id, $this->context->id);
        $badges = [];
        if ($hasbadges) {
            $badges = $hasbadges;

            $hasbadges = true;
        }

        return [
            'showblock' => true,
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'evcs' => (int) $evcs->get_coins(),
            'hasbadges' => $hasbadges,
            'badges' => $badges
        ];
    }
}
