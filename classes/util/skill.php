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

class skill {
    public function get_course_skills_set($courseid, $userid = null) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $courseskills = $this->get_course_skills($courseid);

        if (!$courseskills) {
            return false;
        }

        $skillsids = array_column($courseskills, 'id');

        $skillmoduleutil = new skillmodule();
        $skilluserutil = new skilluser($userid);

        $modulesskillspoints = $skillmoduleutil->get_skills_points_sum($skillsids);
        $userskillspoints = $skilluserutil->get_course_skills_points_sum($courseid);

        $this->fill_course_skills_modules_user_points($courseskills, $modulesskillspoints, $userskillspoints);

        unset($userskillspoints, $modulesskillspoints, $skillmoduleutil, $skilluserutil, $skillsids);

        $data = [];
        foreach ($courseskills as $courseskill) {
            $percentpoints = 0;
            if ($courseskill->userpoints != 0) {
                $percentpoints = (int)(($courseskill->userpoints * 100) / $courseskill->points);
            }

            // TODO: Return renderable data returned by each criteria subplugin.
            $data[] = [
                'id' => $courseskill->id,
                'skill' => $courseskill->name,
                'points' => $courseskill->points,
                'percentpoints' => $percentpoints,
                'userpoints' => $courseskill->userpoints,
                'progressbg' => $this->get_progress_bg($percentpoints)
            ];
        }

        return $data;
    }

    private function fill_course_skills_modules_user_points($courseskills, $moduleskillspoints, $userskillspoints) {
        foreach ($courseskills as $courseskill) {
            $courseskill->points = 0;
            $courseskill->userpoints = 0;

            if (array_key_exists($courseskill->id, $moduleskillspoints)) {
                $courseskill->points = $moduleskillspoints[$courseskill->id]->value;
            }

            if (array_key_exists($courseskill->id, $userskillspoints)) {
                $courseskill->userpoints = $userskillspoints[$courseskill->id]->value;
            }
        }
    }

    private function get_progress_bg($percentpoints) {
        if ($percentpoints > 70) {
            return 'bg-success';
        }

        if ($percentpoints > 50) {
            return 'bg-info';
        }

        return '';
    }

    public function get_course_skills_select($courseid) {
        $skills = $this->get_course_skills($courseid);

        if (!$skills) {
            return [];
        }

        $data = [];
        foreach ($skills as $skill) {
            $data[$skill->id] = $skill->name;
        }

        return $data;
    }

    public function get_course_skills($courseid) {
        global $DB;

        $sql = 'SELECT id, name FROM {evokegame_skills} WHERE courseid = :courseid';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }

    public function create($courseid, $skillname) {
        global $DB;

        $skill = new \stdClass();
        $skill->courseid = $courseid;
        $skill->name = $skillname;
        $skill->timecreated = time();
        $skill->timemodified = time();

        $id = $DB->insert_record('evokegame_skills', $skill);

        $skill->id = $id;

        return $skill;
    }

    public function skill_exists($courseid, $skillname) {
        global $DB;

        return $DB->record_exists('evokegame_skills', ['courseid' => $courseid, 'name' => $skillname]);
    }

    public function delete($skillid) {
        global $DB;

        $skillmodules = $DB->get_records('evokegame_skills_modules', ['skillid' => $skillid]);

        if ($skillmodules) {
            throw new \Exception('You can\'t remove a skill that is being used in an activity.');
        }

        return $DB->delete_records('evokegame_skills', ['id' => $skillid]);
    }
}
