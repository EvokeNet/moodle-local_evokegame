<?php

/**
 * Skill user util class
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

class skilluser {
    protected $userid;

    public function __construct($userid) {
        $this->userid = $userid;
    }

    public function get_skills_points($skillsids) {
        global $DB;

        list($insql, $params) = $DB->get_in_or_equal($skillsids, SQL_PARAMS_NAMED);

        $sql = 'SELECT skillid as id, value
                FROM {evokegame_skills_users}
                WHERE userid = :userid AND skillid ' . $insql;

        $params['userid'] = $this->userid;

        return $DB->get_records_sql($sql, $params);
    }

    public function get_total_skill_points($skillid) {
        global $DB;

        $sql = 'SELECT sm.skillid as id, SUM(su.value) as value
                FROM {evokegame_skills_users} su
                INNER JOIN {evokegame_skills_modules} sm ON su.skillmoduleid = sm.id
                WHERE sm.skillid = :skillid AND su.userid = :userid
                GROUP BY sm.skillid';

        $record = $DB->get_record_sql($sql, ['skillid' => $skillid, 'userid' => $this->userid]);

        if (!$record) {
            return false;
        }

        return (int) $record->value;
    }

    public function get_course_skills_points_sum($courseid) {
        global $DB;

        $sql = 'SELECT sm.skillid as id, SUM(su.value) as value
                FROM {evokegame_skills_users} su
                INNER JOIN {evokegame_skills_modules} sm ON su.skillmoduleid = sm.id
                INNER JOIN {evokegame_skills} s ON s.id = sm.skillid
                WHERE s.courseid = :courseid  AND su.userid = :userid
                GROUP BY sm.skillid';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $this->userid]);

        if (!$records) {
            return false;
        }

        return $records;
    }
}
