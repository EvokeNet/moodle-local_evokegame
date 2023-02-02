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

            $points->add_points('module', 'like', $coursemodulewithcompletion->id, $likeskill, $value);
        }
    }
}
