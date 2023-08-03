<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\util\badge;
use local_evokegame\util\evocoin;
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
class block implements renderable, templatable {
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

        $badgeutil = new badge();

        $points = new point($this->course->id, $USER->id);

        $userutil = new user();

        $badges = $badgeutil->get_course_highlight_badges_with_user_award($USER->id, $this->course->id, $this->context->id);

        $evcs = new evocoin($USER->id);

        return [
            'showblock' => true,
            'evcs' => (int) $evcs->get_coins(),
            'coursename' => $this->course->fullname,
            'contextid' => $this->context->id,
            'points' => (int) $points->mypoints->points,
            'id' => $USER->id,
            'firstname' => $USER->firstname,
            'useravatar' => $userutil->get_user_avatar_or_image($USER),
            'courseid' => $this->course->id,
            'hasbadges' => !empty($badges),
            'badges' => $badges
        ];
    }
}
