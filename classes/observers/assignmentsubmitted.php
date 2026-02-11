<?php

/**
 * Event listener for mod_assign assessable_submitted (assignment_submitted flow).
 *
 * @package     local_evokegame
 * @copyright   2025
 */

namespace local_evokegame\observers;

defined('MOODLE_INTERNAL') || die;

use local_evokegame\util\game;
use local_evokegame\util\point;
use local_evokegame\util\skillmodule;

class assignmentsubmitted {

    /**
     * Observer for assessable_submitted: awards submission skill points to the submitter.
     *
     * @param \mod_assign\event\assessable_submitted $event
     */
    public static function observer(\mod_assign\event\assessable_submitted $event) {
        $courseid = $event->courseid;

        if (!game::is_enabled_in_course($courseid)) {
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

        $points = new point($courseid, $userid);
        foreach ($skillssubmission as $skillpointobject) {
            $points->add_points($skillpointobject);
        }
    }
}
