<?php

/**
 * Badges util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;
class evocoinmodule {
    public function get_module_coins($cmid) {
        global $DB;

        if ($dbcoins = $DB->get_record('evokegame_evcs_modules', ['cmid' => $cmid])) {
            return $dbcoins->value;
        }

        return false;
    }

    public function sync_module_coins($cmid, $educoins = null) {
        global $DB;

        if (!$educoins) {
            return $DB->delete_records('evokegame_evcs_modules', ['cmid' => $cmid]);
        }

        if ($dbcoins = $DB->get_record('evokegame_evcs_modules', ['cmid' => $cmid])) {
            $dbcoins->value = $educoins;
            $dbcoins->timemodified = time();

            return $DB->update_record('evokegame_evcs_modules', $dbcoins);
        }

        $data = new \stdClass();
        $data->cmid = $cmid;
        $data->value = $educoins;
        $data->timecreated = time();
        $data->timemodified = time();

        return $DB->insert_record('evokegame_evcs_modules', $data);
    }
}
