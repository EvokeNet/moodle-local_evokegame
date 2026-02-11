<?php

/**
 * Event listener for mod_assign submission_graded.
 * Ensures skill points are awarded when an assignment is graded, even if
 * \core\event\user_graded is not fired in this Moodle flow.
 *
 * @package     local_evokegame
 * @copyright   2025
 */

namespace local_evokegame\observers;

defined('MOODLE_INTERNAL') || die;

use local_evokegame\util\game;

class assigngraded {

    /**
     * Observer for \mod_assign\event\submission_graded.
     * Awards grading skill points using the same logic as usergraded (generic branch).
     *
     * @param \mod_assign\event\submission_graded $event
     */
    public static function observer($event) {
        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $assign = $event->get_assign();
        $instance = $assign->get_instance();
        $cm = get_coursemodule_from_instance('assign', $instance->id, $event->courseid);

        if (!$cm) {
            return;
        }

        $gradeobj = self::get_grade_for_user($event->courseid, $instance->id, $event->relateduserid);
        if (!$gradeobj) {
            return;
        }

        usergraded::process_generic_grading(
            $event->courseid,
            $event->relateduserid,
            $cm,
            $gradeobj
        );
    }

    /**
     * Load grade from gradebook and return object compatible with get_skill_points_data.
     *
     * @param int $courseid
     * @param int $instanceid Assign instance id
     * @param int $userid
     * @return \stdClass|null Object with rawscaleid, rawgrademax, finalgrade or null
     */
    protected static function get_grade_for_user($courseid, $instanceid, $userid) {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        $gradinginfo = grade_get_grades($courseid, 'mod', 'assign', $instanceid, $userid);
        if (empty($gradinginfo->items)) {
            return null;
        }

        $item = reset($gradinginfo->items);
        $usergrade = isset($item->grades[$userid]) ? $item->grades[$userid] : null;
        if (!$usergrade) {
            return null;
        }

        return (object) [
            'rawscaleid' => $usergrade->rawscaleid ?? 0,
            'rawgrademax' => $usergrade->rawgrademax ?? 100,
            'finalgrade' => $usergrade->grade ?? $usergrade->finalgrade ?? 0,
        ];
    }
}
