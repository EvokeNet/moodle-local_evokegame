<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers\portfoliogroup;

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

        $context = $event->get_context();

        if (!is_enrolled($context, $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $context, $event->relateduserid)) {
            return;
        }

        $cmid = $event->contextinstanceid;

        list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'portfoliogroup');
        $portfoliogroup = $DB->get_record('portfoliogroup', ['id' => $cm->instance], '*', MUST_EXIST);

        $skillmodule = new skillmodule();

        $skillssubmission = $skillmodule->get_module_skills($cmid, 'submission');

        if (!$skillssubmission) {
            return;
        }

        $groupsutil = new \mod_portfoliogroup\util\group();

        $groupmembers = $groupsutil->get_group_members($event->other['groupid'], false);

        if (!$groupmembers) {
            return;
        }

        foreach ($skillssubmission as $skillpointobject) {
            foreach ($groupmembers as $groupmember) {
                // Avoid add points for teachers, admins, anyone who can edit course.
                if (has_capability('moodle/course:update', $context, $groupmember->id)) {
                    continue;
                }

                $groupmemberpoints = new point($event->courseid, $groupmember->id);

                $groupmemberpoints->add_points('module', 'submission', $cmid, $skillpointobject);

                unset($groupmemberpoints);
            }
        }
    }
}
