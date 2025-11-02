<?php

namespace local_evokegame\util\report\chart;

class portfolio {
    public function entries_by_chapter($courseid) {
        $portfolio = new \local_evokegame\util\report\portfolio();
        $data = $portfolio->get_course_total_entries_by_chapter($courseid);

        if (!$data) {
            return false;
        }

        $chart = new \core\chart_pie();

        $chart->set_doughnut(true);

        $series = new \core\chart_series('', array_values($data));

        $chart->add_series($series);

        $chart->set_labels(array_keys($data));

        return $chart;
    }

    public function comments_by_chapter($courseid) {
        $portfolio = new \local_evokegame\util\report\portfolio();
        $data = $portfolio->get_course_total_comments_by_chapter($courseid);

        if (!$data) {
            return false;
        }

        $chart = new \core\chart_pie();

        $chart->set_doughnut(true);

        $series = new \core\chart_series('', array_values($data));

        $chart->add_series($series);

        $chart->set_labels(array_keys($data));

        return $chart;
    }

    public function likes_by_chapter($courseid) {
        $portfolio = new \local_evokegame\util\report\portfolio();
        $data = $portfolio->get_course_total_likes_by_chapter($courseid);

        if (!$data) {
            return false;
        }

        $chart = new \core\chart_pie();

        $chart->set_doughnut(true);

        $series = new \core\chart_series('', array_values($data));

        $chart->add_series($series);

        $chart->set_labels(array_keys($data));

        return $chart;
    }
}
