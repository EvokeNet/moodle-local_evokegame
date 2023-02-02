<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers\portfoliogroup;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\customfield\mod_handler as extrafieldshandler;
use local_evokegame\util\game;
use local_evokegame\util\point;

class commentadded {
    public static function observer(baseevent $event) {
        global $DB;

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        $context = $event->get_context();

        // Only add points for comments made by mentors or site administrators.
        if (!has_capability('moodle/grade:viewall', $context, $event->userid)) {
            return;
        }

        $sql = 'SELECT cm.*
                FROM {course_modules} cm
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = "portfoliogroup"
                WHERE course = :course AND completion <> 0 LIMIT 1';

        $coursemodulewithcompletion = $DB->get_record_sql($sql, ['course' => $event->courseid]);

        if (!$coursemodulewithcompletion) {
            return;
        }

        $handler = extrafieldshandler::create();

        $data = $handler->export_instance_data_object($coursemodulewithcompletion->id);

        $customfields = (array)$data;

        if (!$customfields) {
            return;
        }

        if (!preg_grep('/^comment_/', array_keys($customfields))) {
            // For performance.
            return;
        }

        $groupsutil = new \mod_portfoliogroup\util\group();

        $groupmembers = $groupsutil->get_group_members($event->other['groupid'], false);

        if (!$groupmembers) {
            return;
        }

        foreach ($data as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            $prefixlen = strlen('comment_');

            if (substr($skill, 0, $prefixlen) != 'comment_') {
                continue;
            }

            // String comment_ length == 8.
            $commentskill = substr($skill, $prefixlen);

            foreach ($groupmembers as $groupmember) {
                // Avoid add points for teachers, admins, anyone who can edit course.
                if (has_capability('moodle/course:update', $context, $groupmember->id)) {
                    continue;
                }

                $groupmemberpoints = new point($event->courseid, $groupmember->id);

                $groupmemberpoints->add_points('module', 'comment', $coursemodulewithcompletion->id, $commentskill, $value);

                unset($groupmemberpoints);
            }
        }
    }
}
