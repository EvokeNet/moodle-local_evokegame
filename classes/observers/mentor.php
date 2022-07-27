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

class mentor {
    public static function observer(baseevent $event) {
        global $DB;

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        $context = $event->get_context();

        if (!is_enrolled($context, $event->userid, 'moodle/grade:viewall') && !is_siteadmin()) {
            return;
        }

        $cm = get_coursemodule_from_id('evokeportfolio', $event->contextinstanceid);

        if (!$cm) {
            return;
        }

        $handler = extrafieldshandler::create();

        $data = $handler->export_instance_data_object($cm->id);

        $customfields = (array)$data;

        if (!$customfields) {
            return;
        }

        if (get_class($event) === 'mod_evokeportfolio\event\comment_added') {
            self::add_points($customfields, $event->courseid, $cm->id, $event->relateduserid, 'comment_');
        }

        if (get_class($event) === 'mod_evokeportfolio\event\like_sent') {
            self::add_points($customfields, $event->courseid, $cm->id, $event->relateduserid, 'like_');
        }

        $evokeportfolio = $DB->get_record('evokeportfolio', ['id' => $cm->instance], '*', MUST_EXIST);

        if (!$evokeportfolio->groupactivity) {
            return;
        }

        $groupsutil = new \mod_evokeportfolio\util\group();

        $groupmembersids = [];
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

        if (!$groupmembersids) {
            return;
        }

        foreach ($groupmembersids as $groupmemberid) {
            if (get_class($event) === 'mod_evokeportfolio\event\comment_added') {
                self::add_points($customfields, $event->courseid, $cm->id, $groupmemberid, 'comment_');
            }

            if (get_class($event) === 'mod_evokeportfolio\event\like_sent') {
                self::add_points($customfields, $event->courseid, $cm->id, $groupmemberid, 'like_');
            }
        }
    }


    private static function add_points($customfieldsdata, $courseid, $cmid, $relateduserid, $skillprefix) {
        $points = new point($courseid, $relateduserid);

        foreach ($customfieldsdata as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            // String grading_ length == 8.
            $prefixlen = strlen($skillprefix);

            if (substr($skill, 0, $prefixlen) != $skillprefix) {
                continue;
            }

            $submissionskill = substr($skill, $prefixlen);

            $eventsource = str_replace('_', '', $skillprefix);

            $points->add_points('module', $eventsource, $cmid, $submissionskill, $value);
        }
    }
}
