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
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = 'SELECT id, skill, sum(points) as points
                FROM {evokegame_logs}
                WHERE courseid = :courseid AND userid = :userid
                GROUP BY skill';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

        if (!$records) {
            return false;
        }

        $courseskillspoints = $this->get_course_skills_points_data($courseid);

        $data = [];
        foreach ($records as $record) {
            $points = (int) $record->points;

            $percentpoints = 0;
            $totalpoints = 0;
            if ($points != 0 && !empty($courseskillspoints[$record->skill])) {
                $totalpoints = $courseskillspoints[$record->skill];
                $percentpoints = (int)(($points * 100) / $totalpoints);
            }

            $data[] = [
                'skill' => $record->skill,
                'points' => $points,
                'percentpoints' => $percentpoints,
                'totalpoints' => $totalpoints,
                'progressbg' => $this->get_skill_progress_bg($percentpoints)
            ];
        }

        return $data;
    }

    protected function get_course_skills_points_data($courseid) {
        global $DB;

        $sql = 'SELECT d.id, f.shortname, sum(value) as points
                FROM {customfield_category} c
                INNER JOIN {customfield_field} f ON f.categoryid = c.id
                INNER JOIN {customfield_data} d ON d.fieldid = f.id
                INNER JOIN {course_modules} cm ON cm.id = d.instanceid
                WHERE c.component = "local_evokegame" AND c.area = "mod" AND cm.course = :courseid
                GROUP BY f.shortname';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $skillname = $record->shortname;

            if (strpos($skillname, 'submission_') === false && strpos($skillname, 'grading_') === false) {
                continue;
            }

            $evaluations = ['submission_', 'grading_'];
            $skillname = str_replace($evaluations, "", $skillname);

            if (!empty($data[$skillname])) {
                $data[$skillname] += $record->points;

                continue;
            }

            $data[$skillname] = $record->points;
        }

        return $data;
    }

    private function get_skill_progress_bg($percentpoints) {
        if ($percentpoints > 70) {
            return 'bg-success';
        }

        if ($percentpoints > 50) {
            return 'bg-info';
        }

        return '';
    }

    public function get_course_skills_select($courseid) {
        global $DB;

        $sql = 'SELECT d.id, f.shortname
                FROM {customfield_category} c
                INNER JOIN {customfield_field} f ON f.categoryid = c.id
                INNER JOIN {customfield_data} d ON d.fieldid = f.id
                INNER JOIN {course_modules} cm ON cm.id = d.instanceid
                WHERE c.component = "local_evokegame" AND c.area = "mod" AND cm.course = :courseid
                GROUP BY f.shortname
                ORDER BY d.id';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        $data = [
            get_string('chooseanoption', 'local_evokegame')
        ];
        foreach ($records as $record) {
            $skillname = $record->shortname;

            if (strpos($skillname, 'submission_') === false && strpos($skillname, 'grading_') === false) {
                continue;
            }

            $evaluations = ['submission_', 'grading_'];
            $skillname = str_replace($evaluations, "", $skillname);

            if (in_array($skillname, $data)) {
                continue;
            }

            $data[] = $skillname;
        }

        return $data;
    }

    public function get_skill_string_name($courseid, $skillid) {
        $skillsselect = $this->get_course_skills_select($courseid);

        return $skillsselect[$skillid];
    }
}
