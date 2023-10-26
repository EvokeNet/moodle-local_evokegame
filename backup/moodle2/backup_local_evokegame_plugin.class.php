<?php

/**
 * Defines backup_local_evokegame class.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

/**
 * Backup plugin class.
 *
 * @package    local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class backup_local_evokegame_plugin extends backup_local_plugin {

    /**
     * Returns the format information to attach to course element.
     */
    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();
        $evokegame = new backup_nested_element('evokegame');

        $skills = new backup_nested_element('skills');
        $skill = new backup_nested_element('skill', ['id'], ['courseid', 'name']);

        $plugin->add_child($evokegame);
        $evokegame->add_child($skills);
        $skills->add_child($skill);

        $skill->set_source_table('evokegame_skills', ['courseid' => backup::VAR_COURSEID]);

        return $plugin;
    }

    protected function define_module_plugin_structure() {
        $plugin = $this->get_plugin_element();
        $evokegame = new backup_nested_element('evokegame');
        $evocoins = new backup_nested_element('evocoins', ['id'], ['cmid', 'value']);

        $plugin->add_child($evokegame);
        $evokegame->add_child($evocoins);

        if ($dataforbackup = $this->get_activity_evocoins($this->task->get_moduleid())) {
            $evocoins->set_source_array($dataforbackup);
        }

        $skills = new backup_nested_element('skills');
        $skill = new backup_nested_element('skill', ['id'], ['skillid', 'cmid', 'value', 'action']);

        $evokegame->add_child($skills);
        $skills->add_child($skill);

        if ($dataforbackup = $this->get_activity_skills($this->task->get_moduleid())) {
            $skill->set_source_array($dataforbackup);
        }

        return $plugin;
    }

    private function get_activity_evocoins($cmid) {
        global $DB;

        $records = $DB->get_records('evokegame_evcs_modules', ['cmid' => $cmid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $data[] = (array) $record;
        }

        return $data;
    }

    private function get_activity_skills($cmid) {
        global $DB;

        $records = $DB->get_records('evokegame_skills_modules', ['cmid' => $cmid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $data[] = (array) $record;
        }

        return $data;
    }
}
