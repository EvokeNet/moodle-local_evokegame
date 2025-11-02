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
class badgecriterias implements renderable, templatable {
    protected $course;
    protected $context;
    protected $evokebadge;

    public function __construct($course, $context, $evokebadge) {
        $this->course = $course;
        $this->context = $context;
        $this->evokebadge = $evokebadge;
    }

    public function export_for_template(renderer_base $output) {
        $badgecriteriautil = new \local_evokegame\util\badgecriteria();

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'badgeid' => $this->evokebadge->id,
            'criterias' => $badgecriteriautil->get_evoke_badge_criterias_with_skill_name($this->evokebadge)
        ];
    }
}
