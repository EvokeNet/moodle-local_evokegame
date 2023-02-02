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
    public static function mod_evokeportfolio(baseevent $event) {
        global $DB;

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $cmid = $event->contextinstanceid;

        $handler = extrafieldshandler::create();

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

    public static function mod_portfoliobuilder(baseevent $event) {
        global $DB;

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $sql = 'SELECT cm.*
                FROM {course_modules} cm
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = "portfoliobuilder"
                WHERE course = :course AND completion <> 0 LIMIT 1';

        $coursemodulewithcompletion = $DB->get_record_sql($sql, ['course' => $event->courseid]);

        if (!$coursemodulewithcompletion) {
            return;
        }

        $handler = extrafieldshandler::create();

        $data = $handler->export_instance_data_object($coursemodulewithcompletion->id);

        if (!preg_grep('/^submission_/', array_keys((array)$data))) {
            // For performance.
            return;
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

            $points->add_points('module', 'submission', $coursemodulewithcompletion->id, $submissionskill, $value);
        }
    }

    public static function mod_portfoliogroup(baseevent $event) {
        global $DB;

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $sql = 'SELECT cm.*
                FROM {course_modules} cm
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = "portfoliobuilder"
                WHERE course = :course AND completion <> 0 LIMIT 1';

        $coursemodulewithcompletion = $DB->get_record_sql($sql, ['course' => $event->courseid]);

        if (!$coursemodulewithcompletion) {
            return;
        }

        $handler = extrafieldshandler::create();

        $data = $handler->export_instance_data_object($coursemodulewithcompletion->id);

        if (!preg_grep('/^submission_/', array_keys((array)$data))) {
            // For performance.
            return;
        }

        $groupsutil = new \mod_portfoliogroup\util\group();

        $groupmembers = $groupsutil->get_group_members($event->groupid, false);

        if (!$groupmembers) {
            return;
        }

        foreach ($data as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            if (substr($skill, 0, 11) != 'submission_') {
                continue;
            }

            // String submission_ length == 11.
            $submissionskill = substr($skill, 11);

            foreach ($groupmembers as $groupmember) {
                $groupmemberpoints = new point($event->courseid, $groupmember->id);

                $groupmemberpoints->add_points('module', 'submission', $coursemodulewithcompletion->id, $submissionskill, $value);

                unset($groupmemberpoints);
            }
        }
    }
}
