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
use local_evokegame\util\game;
use local_evokegame\util\point;
use local_evokegame\util\skillmodule;

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

        $cmid = $event->contextinstanceid;

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'portfoliobuilder');
        $portfoliobuilder = $DB->get_record('portfoliobuilder', ['id' => $cm->instance], '*', MUST_EXIST);

        $skillmodule = new skillmodule();

        $skillssubmission = $skillmodule->get_module_skills($cmid, 'submission');

        if (!$skillssubmission) {
            return;
        }

        $points = new point($event->courseid, $event->relateduserid);

        foreach ($skillssubmission as $skillpointobject) {
            $points->add_points('module', 'submission', $cmid, $skillpointobject);
        }
    }
}
