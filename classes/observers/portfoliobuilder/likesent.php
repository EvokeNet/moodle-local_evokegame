<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers\portfoliobuilder;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\customfield\mod_handler as extrafieldshandler;
use local_evokegame\util\game;
use local_evokegame\util\point;

class likesent {
    public static function observer(baseevent $event) {
        global $DB;

        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        $context = $event->get_context();

        // Only add points to enrolled users.
        if (!is_enrolled($context, $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $context, $event->relateduserid)) {
            return;
        }

        // Only add points for likes made by mentors or site administrators.
        if (!has_capability('moodle/grade:viewall', $context, $event->userid)) {
            return;
        }

        $cmid = $event->contextinstanceid;

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'portfoliobuilder');
        $portfoliobuilder = $DB->get_record('portfoliobuilder', ['id' => $cm->instance], '*', MUST_EXIST);

        $handler = extrafieldshandler::create();

        $data = $handler->export_instance_data_object($cmid);

        $customfields = (array)$data;

        if (!$customfields) {
            return;
        }

        if (!preg_grep('/^like_/', array_keys($customfields))) {
            // For performance.
            return;
        }

        $points = new point($event->courseid, $event->relateduserid);

        foreach ($data as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            $prefixlen = strlen('like_');

            if (substr($skill, 0, $prefixlen) != 'like_') {
                continue;
            }

            // String like_ length == 5.
            $likeskill = substr($skill, $prefixlen);

            $points->add_points('module', 'like', $cmid, $likeskill, $value);
        }
    }
}
