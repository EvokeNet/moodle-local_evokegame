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

class skillmodule {
    public function get_module_skills($cmid, $action) {
        global $DB;

        $sql = 'SELECT skillid as id, value FROM {evokegame_skills_modules} WHERE cmid = :cmid AND action = :action';

        return $DB->get_records_sql($sql, ['cmid' => $cmid, 'action' => $action]);
    }

    public function get_skills_points_sum($skillsids) {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal($skillsids);

        $sql = 'SELECT skillid, SUM(value) as value FROM {evokegame_skills_modules} WHERE skillid '. $insql .' GROUP BY skillid';

        return $DB->get_records_sql($sql, $inparams);
    }

    public function sync_module_skills($cmid, $action, $skills = []) {
        global $DB;

        if (empty($skills)) {
            return $DB->delete_records('evokegame_skills_modules', ['cmid' => $cmid, 'action' => $action]);
        }

        $dbpoints = $DB->get_records('evokegame_skills_modules', ['cmid' => $cmid, 'action' => $action]);

        foreach ($skills as $skillid => $value) {
            if (!$value) {
                continue;
            }

            if (!$dbpoints) {
                $this->save($skillid, $cmid, $action, $value);

                continue;
            }

            foreach ($dbpoints as $key => $dbpoint) {
                if ($dbpoint->skillid == $skillid) {
                    if ($dbpoint->value != $value) {
                        $dbpoint->value = $value;
                        $dbpoint->timemodified = time();

                        $DB->update_record('evokegame_skills_modules', $dbpoint);
                    }

                    unset($dbpoints[$key]);

                    continue 2;
                }
            }

            $this->save($skillid, $cmid, $action, $value);
        }

        if ($dbpoints) {
            foreach ($dbpoints as $dbpoint) {
                $DB->delete_records('evokegame_skills_modules', ['id' => $dbpoint->id]);
            }
        }
    }

    public function save($skillid, $cmid, $action, $value) {
        global $DB;

        $data = new \stdClass();
        $data->skillid = $skillid;
        $data->cmid = $cmid;
        $data->value = $value;
        $data->action = $action;
        $data->timecreated = time();
        $data->timemodified = time();

        return $DB->insert_record('evokegame_skills_modules', $data);
    }
}
