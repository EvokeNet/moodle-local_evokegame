<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers\evokeportfolio;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\util\game;
use local_evokegame\util\point;
use local_evokegame\util\skillmodule;

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

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $cm = get_coursemodule_from_id('evokeportfolio', $event->contextinstanceid);

        if (!$cm) {
            return;
        }

        $skillmodule = new skillmodule();

        if (get_class($event) === 'mod_evokeportfolio\event\comment_added') {
            $skillscomment = $skillmodule->get_module_skills($cm->id, 'comment');

            if ($skillscomment) {
                self::add_points($skillscomment, $event->courseid, $event->relateduserid);
            }
        }

        if (get_class($event) === 'mod_evokeportfolio\event\like_sent') {
            $skillslike = $skillmodule->get_module_skills($cm->id, 'like');

            if ($skillslike) {
                self::add_points($skillslike, $event->courseid, $event->relateduserid);
            }
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
            if (get_class($event) === 'mod_evokeportfolio\event\comment_added' && isset($skillscomment)) {
                self::add_points($skillscomment, $event->courseid, $groupmemberid);
            }

            if (get_class($event) === 'mod_evokeportfolio\event\like_sent' && isset($skillslike)) {
                self::add_points($skillslike, $event->courseid, $groupmemberid);
            }
        }
    }

    private static function add_points($skillsgrade, $courseid, $relateduserid) {
        $points = new point($courseid, $relateduserid);

        foreach ($skillsgrade as $skillpointobject) {
            $points->add_points($skillpointobject);
        }
    }
}
