<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class coursesettings implements renderable, templatable {
    protected $course;
    protected $context;
    protected $form;

    public function __construct($course, $context, $form) {
        $this->course = $course;
        $this->context = $context;
        $this->form = $form;
    }

    public function export_for_template(renderer_base $output) {

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'courusename' => $this->course->fullname,
            'form' => $this->form->render()
        ];
    }
}
