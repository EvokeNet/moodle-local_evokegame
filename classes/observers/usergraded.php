<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\customfield\mod_handler as extrafieldshandler;
use local_evokegame\util\game;
use local_evokegame\util\point;

class usergraded {
    public static function observer(baseevent $event) {
        global $DB;

        $handler = extrafieldshandler::create();

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        $gradeitemid = $event->other['itemid'];

        $gradeitem = self::get_grade_item($gradeitemid);

        if (!$gradeitem || $gradeitem->itemtype != 'mod') {
            return;
        }

        $cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance);

        if (!$cm) {
            return;
        }

        $data = $handler->export_instance_data_object($cm->id);

        if (!preg_grep('/^grading_/', array_keys((array)$data))) {
            // For performance.
            return;
        }

        $evokeportfolio = $DB->get_record('evokeportfolio', ['id' => $cm->instance], '*', MUST_EXIST);

        $groupmembersids = [];
        if ($evokeportfolio->groupactivity) {
            $groupsutil = new \mod_evokeportfolio\util\group();

            if ($usercoursegroups = $groupsutil->get_user_groups($evokeportfolio->course, $event->relateduserid)) {
                if ($groupsmembers = $groupsutil->get_groups_members($usercoursegroups, false)) {
                    foreach ($groupsmembers as $groupsmember) {
                        // Skip current user.
                        if ($groupsmember->id == $event->relateduserid) {
                            continue;
                        }

                        $groupmembersids[] = $groupsmember->id;
                    }
                }
            }
        }

        $points = new point($event->courseid, $event->relateduserid);

        foreach ($data as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            if (substr($skill, 0, 8) != 'grading_') {
                continue;
            }

            // String submission_ length == 8.
            $submissionskill = substr($skill, 8);

//            $pointstoadd = $event->get_grade()->finalgrade * $value;
            $pointstoadd = $value;

            $points->add_points('module', 'grading', $cm->id, $submissionskill, $pointstoadd);

            if (!$evokeportfolio->groupactivity && !$groupmembersids) {
                continue;
            }

            foreach ($groupmembersids as $groupmemberid) {
                $groupmemberpoints = new point($event->courseid, $groupmemberid);

                $groupmemberpoints->add_points('module', 'grading', $cm->id, $submissionskill, $pointstoadd);

                unset($groupmemberpoints);
            }
        }
    }

    protected static function get_grade_item($itemid) {
        global $DB;

        return $DB->get_record('grade_items', ['id' => $itemid]);
    }
}
