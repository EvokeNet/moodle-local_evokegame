<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers\portfoliobuilder;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\customfield\mod_handler as extrafieldshandler;
use local_evokegame\util\game;
use local_evokegame\util\point;

class entryadded {
    public static function observer(baseevent $event) {
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

            $prefixlen = strlen('submission_');

            if (substr($skill, 0, $prefixlen) != 'submission_') {
                continue;
            }

            // String submission_ length == 11.
            $submissionskill = substr($skill, $prefixlen);

            $points->add_points('module', 'submission', $coursemodulewithcompletion->id, $submissionskill, $value);
        }
    }
}
