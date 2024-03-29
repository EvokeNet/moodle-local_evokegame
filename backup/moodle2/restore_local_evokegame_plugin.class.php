<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines restore_local_evokegame class.
 *
 * @package     local_evokegame
 * @author      Dan Marsden
 * @copyright   2018 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore plugin class.
 *
 * @package    local_evokegame
 * @author     Dan Marsden http://danmarsden.com
 * @copyright  2018 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_evokegame_plugin extends restore_local_plugin {
    /**
     * Returns the paths to be handled by the plugin at course level.
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        $paths[] = new restore_path_element('evokegame_skills', '/course/evokegame/skills/skill');
        $paths[] = new restore_path_element('evokegame_badges', '/course/evokegame/badges/badge');
        $paths[] = new restore_path_element('evokegame_badges_criterias', '/course/evokegame/badges/badge/badgecriterias/criteria');

        return $paths;
    }

    public function define_module_plugin_structure() {
        $paths = [];

        $paths[] = new restore_path_element('evokegame_evocoins', '/module/evokegame/evocoins');
        $paths[] = new restore_path_element('evokegame_moduleskills', '/module/evokegame/skills/skill');

        return $paths;
    }


    /**
     * Process local_evokegame_cc table.
     * @param stdClass $data
     */
    public function process_evokegame_skills($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->courseid = $this->task->get_courseid();
        $data->timecreated = time();
        $data->timemodified = time();

        $newitemid = $DB->insert_record('evokegame_skills', $data);

        $this->set_mapping('skill', $oldid, $newitemid);
    }

    public function process_evokegame_badges($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->courseid = $this->task->get_courseid();
        $data->timecreated = time();
        $data->timemodified = time();

        $newitemid = $DB->insert_record('evokegame_badges', $data);

        $this->set_mapping('evokebadge', $oldid, $newitemid);
    }

    public function process_evokegame_badges_criterias($data) {
        global $DB;

        $data = (object) $data;

        $data->courseid = $this->task->get_courseid();
        $data->evokebadgeid = $this->get_mappingid('evokebadge', $data->evokebadgeid);
        $data->timecreated = time();
        $data->timemodified = time();

        if ($data->method == 'skillpoints') {
            $data->target = $this->get_mappingid('skill', $data->target);
        }

        if ($data->method == 'skillpointsaggregation') {
            $skills = explode(',', $data->target);

            $skills = array_map(function($skill) {
                return $this->get_mappingid('skill', $skill);
            }, $skills);

            $data->target = implode(',', $skills);
        }

        $DB->insert_record('evokegame_badges_criterias', $data);
    }

    public function process_evokegame_evocoins($data) {
        global $DB;

        if (!$data) {
            return;
        }

        $data['cmid'] = $this->task->get_moduleid();
        $data['timecreated'] = time();
        $data['timemodified'] = time();

        $DB->insert_record('evokegame_evcs_modules', $data);
    }

    public function process_evokegame_moduleskills($data) {
        global $DB;

        if (!$data) {
            return;
        }

        if ($skillid = $this->get_mappingid('skill', $data['skillid'])) {
            $data['skillid'] = $skillid;
        }

        $data['cmid'] = $this->task->get_moduleid();
        $data['timecreated'] = time();
        $data['timemodified'] = time();

        $DB->insert_record('evokegame_skills_modules', $data);
    }

    public function after_restore_course() {
        if (get_config('local_evokegame', 'isgameenabledincourse-' . $this->task->get_old_courseid())) {
            set_config('isgameenabledincourse-' . $this->task->get_courseid(), 1, 'local_evokegame');
        }

        $this->sync_badges_ids();
    }

    private function sync_badges_ids() {
        global $DB;

        $courseid = $this->task->get_courseid();

        $evokebadges = $DB->get_records('evokegame_badges', ['courseid' => $courseid]);

        if (!$evokebadges) {
            return;
        }

        foreach ($evokebadges as $evokebadge) {
            $badge = $DB->get_record('badge', ['courseid' => $evokebadge->courseid, 'name' => $evokebadge->name]);

            if (!$badge) {
                continue;
            }

            $evokebadge->badgeid = $badge->id;

            $DB->update_record('evokegame_badges', $evokebadge);

            if (!$badge->status) {
                $badge->status = 1;

                $DB->update_record('badge', $badge);
            }
        }
    }
}
