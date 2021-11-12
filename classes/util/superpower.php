<?php

/**
 * Skill util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

class superpower {
    public function get_course_superpowers($courseid) {
        global $DB;

        $records = $DB->get_records('evokegame_superpowers', ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }
}
