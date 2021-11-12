<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use local_evokegame\util\superpower as superpowerutil;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class superpower implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        $superpowerutil = new superpowerutil();

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'superpowers' => $superpowerutil->get_course_superpowers($this->course->id)
        ];
    }
}
