<?php

/**
 * Evoke renderer callable class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die;

class evokegame {
    public function get_dashboard($course, $context) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_evokegame');

        $contentrenderable = new \local_evokegame\output\dashboard($course, $context);

        return $renderer->render($contentrenderable);
    }
}