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

class submissionsent {
    public static function observer(baseevent $event) {
        global $DB;

        $handler = extrafieldshandler::create();

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        $cmid = $event->contextinstanceid;

        $data = $handler->export_instance_data_object($cmid);

        if (!preg_grep('/^submission_/', array_keys((array)$data))) {
            // For performance.
            return;
        }

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'evokeportfolio');
        $evokeportfolio = $DB->get_record('evokeportfolio', ['id' => $cm->instance], '*', MUST_EXIST);

        $groupmembersids = [];
        if ($evokeportfolio->groupactivity) {
            $groupsutil = new \mod_evokeportfolio\util\group();

            if ($usercoursegroups = $groupsutil->get_user_groups($course->id)) {
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

            if (substr($skill, 0, 11) != 'submission_') {
                continue;
            }

            // String submission_ length == 11.
            $submissionskill = substr($skill, 11);

            $points->add_points('module', 'submission', $event->contextinstanceid, $submissionskill, $value);

            if ($evokeportfolio->groupactivity && $groupmembersids) {
                foreach ($groupmembersids as $groupmemberid) {
                    $groupmemberpoints = new point($event->courseid, $groupmemberid);

                    $groupmemberpoints->add_points('module', 'submission', $event->contextinstanceid, $submissionskill, $value);

                    unset($groupmemberpoints);
                }
            }
        }
    }
}
