<?php

/**
 * Points util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

class points {
    protected $dbpoints;

    public function __construct($courseid, $userid) {
        $this->dbpoints = $this->get_user_course_points($courseid, $userid);
    }

    public function add_points($pointsource, $pointsourcetype, $sourceid, $skill, $points) {
        global $DB;

        if ($this->points_already_added($pointsource, $pointsourcetype, $sourceid, $skill)) {
            return;
        }

        $this->dbpoints->points += $points;

        $DB->update_record('evokegame_points', $this->dbpoints);

        $this->insert_log_point_entry($pointsource, $pointsourcetype, $sourceid, $skill, $points);
    }

    public function get_user_course_points($courseid, $userid) {
        global $DB;

        $points = $DB->get_record('evokegame_points', ['courseid' => $courseid, 'userid' => $userid]);

        if ($points) {
            return $points;
        }

        $points = new \stdClass();
        $points->courseid = $courseid;
        $points->userid = $userid;
        $points->points = 0;
        $points->timecreated = time();
        $points->timemodified = time();

        $insertedid = $DB->insert_record('evokegame_points', $points);

        $points->id = $insertedid;

        return $points;
    }

    public function insert_log_point_entry($pointsource, $pointsourcetype, $sourceid, $skill, $points) {
        global $DB;

        $pointsdata = new \stdClass();
        $pointsdata->courseid = $this->dbpoints->courseid;
        $pointsdata->userid = $this->dbpoints->userid;
        $pointsdata->pointsource = $pointsource;
        $pointsdata->pointsourcetype = $pointsourcetype;
        $pointsdata->sourceid = $sourceid;
        $pointsdata->skill = $skill;
        $pointsdata->points = $points;
        $pointsdata->timecreated = time();
        $pointsdata->timemodified = time();

        return $DB->insert_record('evokegame_logs', $pointsdata);
    }

    public function points_already_added($pointsource, $pointsourcetype, $sourceid, $skill) {
        global $DB;

        $records = $DB->get_records('evokegame_logs', [
            'courseid' => $this->dbpoints->courseid,
            'userid' => $this->dbpoints->userid,
            'pointsource' => $pointsource,
            'pointsourcetype' => $pointsourcetype,
            'sourceid' => $sourceid,
            'skill' => $skill
        ]);

        if ($records) {
            return true;
        }

        return false;
    }
}
