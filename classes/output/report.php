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
        $evocoins = new \local_evokegame\util\report\evocoins();
        $portfolio = new \local_evokegame\util\report\portfolio();
        $skills = new \local_evokegame\util\report\skills();
        $students = new \local_evokegame\util\report\students();

        $totalevocoins = $evocoins->get_course_total($this->course->id);

        $totalstudents = $students->get_course_total($this->context);

        $totalpossiblecoins = $totalevocoins * $totalstudents;

        $totaldistributedevocoins = $evocoins->get_course_total_distributed($this->course->id);

        $courseskills = $skills->get_course_skills_with_totalpoints($this->course->id);

        $totalskillspoints = array_reduce($courseskills, function($carry, $item) {
            $carry += $item->value;

            return $carry;
        });

        $evocoinsdistributionprogress = (int)(ceil($totaldistributedevocoins * 100 / $totalpossiblecoins));

        $portfoliochart = new \local_evokegame\util\report\chart\portfolio();

        $entriesbychapter = $portfoliochart->entries_by_chapter($this->course->id);
        if ($entriesbychapter) {
            $entriesbychapter = $output->render($entriesbychapter);
        }

        $likesbychapter = $portfoliochart->likes_by_chapter($this->course->id);
        if ($likesbychapter) {
            $likesbychapter = $output->render($likesbychapter);
        }

        $commentsbychapter = $portfoliochart->comments_by_chapter($this->course->id);
        if ($commentsbychapter) {
            $commentsbychapter = $output->render($commentsbychapter);
        }

        return [
            'courseevocoins' => $totalevocoins,
            'totalstudents' => $totalstudents,
            'totalpossiblecoins' => $totalpossiblecoins,
            'totalskillspoints' => $totalskillspoints,
            'totaldistributedevocoins' => $totaldistributedevocoins,
            'evocoinsdistributionprogress' => $evocoinsdistributionprogress,
            'courseskills' => $courseskills,
            'totalportfolioentries' => $portfolio->get_course_total_entries($this->course->id),
            'totalportfoliolikes' => $portfolio->get_course_total_likes($this->course->id),
            'totalportfoliocomments' => $portfolio->get_course_total_comments($this->course->id),
            'chartentriesbychapter' => $entriesbychapter,
            'chartlikesbychapter' => $likesbychapter,
            'chartcommentsbychapter' => $commentsbychapter,
        ];
    }
}
