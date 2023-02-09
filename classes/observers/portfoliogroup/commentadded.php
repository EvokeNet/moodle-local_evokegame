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

        $cmid = $event->contextinstanceid;

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'portfoliogroup');
        $portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);

        $handler = extrafieldshandler::create();

        $data = $handler->export_instance_data_object($cmid);

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

                $groupmemberpoints->add_points('module', 'comment', $cmid, $commentskill, $value);

                unset($groupmemberpoints);
            }
        }
    }
}
