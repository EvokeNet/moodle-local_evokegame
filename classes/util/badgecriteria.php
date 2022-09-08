<?php

/**
 * Evoke badge criterias util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

class badgecriteria {
    public function get_evoke_badge_criterias($evokebadgeid) {
        global $DB;

        $records = $DB->get_records('evokegame_badges_criterias', ['evokebadgeid' => $evokebadgeid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }
}
