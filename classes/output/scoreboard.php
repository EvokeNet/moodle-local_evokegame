<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\table\scoreboard as scoreboardtable;
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
        $table = new scoreboardtable(
            'local-evokegame-scoreboard-table',
            $this->context,
            $this->course
        );

        $table->collapsible(false);

        ob_start();
        $table->out(30, true);
        $scoreboard = ob_get_contents();
        ob_end_clean();

        return [
            'courseid' => $this->course->id,
            'scoreboard' => $scoreboard,
        ];
    }
}
