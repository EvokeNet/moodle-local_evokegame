<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\table\scoreboard as scoreboardtable;
use local_evokegame\util\course;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class scoreboard implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        $scoreboardutil = new \local_evokegame\util\scoreboard($this->course, $this->context);
        $courseutil = new course();

        $scoreboardprize = get_config('local_evokegame', 'scoreboard_prize-' . $this->course->id);
        $scoreboardfinishdate = get_config('local_evokegame', 'scoreboard_finishdate-' . $this->course->id);
        $timeremaining = $scoreboardfinishdate - time();

        return [
            'courseid' => $this->course->id,
            'courseimage' => $courseutil->get_summary_image_url($this->course, $this->context),
            'scoreboard' => $scoreboardutil->get_scoreboard(0, 10),
            'scoreboardfinishdate' => $timeremaining > 0 ? userdate($scoreboardfinishdate) : false,
            'scoreboardprize' => $scoreboardprize
        ];
    }
}
