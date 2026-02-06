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
    private const LOG_PREFIX = '[evokegame assignmentsubmitted]';

    /**
     * Observer for assessable_submitted: awards submission skill points to the submitter.
     *
     * @param \mod_assign\event\assessable_submitted $event
     */
    public static function observer(\mod_assign\event\assessable_submitted $event) {
        $courseid = $event->courseid;
        $contextinstanceid = $event->contextinstanceid ?? 0;
        $eventuserid = $event->userid ?? 0;
        $eventrelateduserid = $event->relateduserid ?? null;

        debugging(
            self::LOG_PREFIX . " event received | courseid={$courseid} contextinstanceid={$contextinstanceid} userid={$eventuserid} relateduserid=" . ($eventrelateduserid ?? 'null'),
            DEBUG_NORMAL
        );

        if (!game::is_enabled_in_course($courseid)) {
            debugging(self::LOG_PREFIX . " skip: game not enabled in course {$courseid}", DEBUG_NORMAL);
            return;
        }

        $userid = $event->relateduserid ?? $event->userid;
        if (empty($userid)) {
            debugging(self::LOG_PREFIX . " skip: userid empty (userid={$eventuserid} relateduserid=" . ($eventrelateduserid ?? 'null') . ")", DEBUG_NORMAL);
            return;
        }

        if (!is_enrolled($event->get_context(), $userid)) {
            debugging(self::LOG_PREFIX . " skip: user {$userid} not enrolled in context", DEBUG_NORMAL);
            return;
        }

        if (has_capability('moodle/course:update', $event->get_context(), $userid)) {
            debugging(self::LOG_PREFIX . " skip: user {$userid} has course:update (teacher/admin)", DEBUG_NORMAL);
            return;
        }

        $cmid = $event->contextinstanceid;

        $skillmodule = new skillmodule();
        $skillssubmission = $skillmodule->get_module_skills($cmid, 'submission');

        if (!$skillssubmission) {
            debugging(self::LOG_PREFIX . " skip: no submission skills for cmid={$cmid} (course {$courseid})", DEBUG_NORMAL);
            return;
        }

        debugging(
            self::LOG_PREFIX . " adding points | courseid={$courseid} userid={$userid} cmid={$cmid} skills_count=" . count($skillssubmission),
            DEBUG_NORMAL
        );

        $points = new point($courseid, $userid);
        foreach ($skillssubmission as $skillpointobject) {
            $points->add_points($skillpointobject);
        }
    }
}
