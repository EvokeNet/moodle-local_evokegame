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

class point {
    public $mypoints;

    public function __construct($courseid, $userid) {
        $this->mypoints = $this->get_user_course_points($courseid, $userid);
    }

    public function add_points($skillpointobject) {
        global $DB, $USER;

        if ($this->points_already_added($skillpointobject->skillmoduleid)) {
            return;
        }

        $this->mypoints->points += $skillpointobject->value;

        $DB->update_record('evokegame_points', $this->mypoints);

        $this->insert_skills_users_entry($skillpointobject);

        if ($USER->id === $this->mypoints->userid) {
            \core\notification::success(get_string('toastr_skillpoints', 'local_evokegame'));
        }
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

    public function insert_skills_users_entry($skillpointobject) {
        global $DB;

        $pointsdata = new \stdClass();
        $pointsdata->skillmoduleid = $skillpointobject->skillmoduleid;
        $pointsdata->userid = $this->mypoints->userid;
        $pointsdata->value = $skillpointobject->value;
        $pointsdata->timecreated = time();
        $pointsdata->timemodified = time();

        $id = $DB->insert_record('evokegame_skills_users', $pointsdata);
        $pointsdata->id = $id;

        $context = \context_course::instance($this->mypoints->courseid);

        $params = array(
            'context' => $context,
            'objectid' => $this->mypoints->id,
            'courseid' => $this->mypoints->courseid,
            'relateduserid' => $this->mypoints->userid
        );
        $event = \local_evokegame\event\points_added::create($params);
        $event->trigger();
    }

    public function points_already_added($skillmoduleid) {
        global $DB;

        $records = $DB->get_records('evokegame_skills_users', [
            'skillmoduleid' => $skillmoduleid,
            'userid' => $this->mypoints->userid
        ]);

        if ($records) {
            return true;
        }

        return false;
    }
}
