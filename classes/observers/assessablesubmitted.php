<?php

/**
 * Event listener for assessable submissions (generic for modules like assignment).
 *
 * @package     local_evokegame
 * @copyright   2025
 */

namespace local_evokegame\observers;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\util\game;
use local_evokegame\util\point;
use local_evokegame\util\skillmodule;

class assessablesubmitted {
    public static function observer(baseevent $event) {
        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        $userid = $event->relateduserid ?? $event->userid;
        if (empty($userid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $userid)) {
            return;
        }

        if (has_capability('moodle/course:update', $event->get_context(), $userid)) {
            return;
        }

        $cmid = $event->contextinstanceid;

        $skillmodule = new skillmodule();
        $skillssubmission = $skillmodule->get_module_skills($cmid, 'submission');

        if (!$skillssubmission) {
            return;
        }

        $points = new point($event->courseid, $userid);
        foreach ($skillssubmission as $skillpointobject) {
            $points->add_points($skillpointobject);
        }
    }
}
