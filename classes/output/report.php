<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class report implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        $report = new \local_evokegame\util\report();

        $totalevocoins = $report->get_course_total_evocoins($this->course->id);

        $totalstudents = $report->get_course_total_students($this->context, $this->course->id);

        $totalpossiblecoins = $totalevocoins * $totalstudents;

        $totaldistributedevocoins = $report->get_course_total_distributed_evocoins($this->course->id);

        $courseskills = $report->get_course_skills_with_totalpoints($this->course->id);

        $totalskillspoints = array_reduce($courseskills, function($carry, $item) {
            $carry += $item->value;

            return $carry;
        });

        $evocoinsdistributionprogress = (int)(ceil($totaldistributedevocoins * 100 / $totalpossiblecoins));

        return [
            'courseevocoins' => $totalevocoins,
            'totalstudents' => $totalstudents,
            'totalpossiblecoins' => $totalpossiblecoins,
            'totalskillspoints' => $totalskillspoints,
            'totaldistributedevocoins' => $totaldistributedevocoins,
            'evocoinsdistributionprogress' => $evocoinsdistributionprogress,
            'courseskills' => $courseskills
        ];
    }
}
