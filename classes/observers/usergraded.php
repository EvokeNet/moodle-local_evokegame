<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\observers;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_evokegame\util\game;
use local_evokegame\util\point;
use local_evokegame\util\skillmodule;

class usergraded {
    public static function observer(baseevent $event) {
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

        $grade = $event->get_grade();
        $gradeitem = $grade->grade_item;

        if ($gradeitem->itemtype != 'mod') {
            return;
        }

        $cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance);

        if (!$cm) {
            return;
        }

        $skillmodule = new skillmodule();

        $skillsgrade = $skillmodule->get_module_skills($cm->id, 'grading');

        if (!$skillsgrade) {
            return;
        }

        if ($gradeitem->itemmodule == 'evokeportfolio') {
            self::handle_evokeportfolio($event, $cm, $skillsgrade);
        }

        if ($gradeitem->itemmodule == 'portfoliobuilder' || $gradeitem->itemmodule == 'portfoliogroup') {
            self::handle_portfoliobuilder_or_portfoliogroup($event, $cm, $skillsgrade);
        } else {
            // Generic handling for other modules (e.g., assignment).
            $userpoints = new point($event->courseid, $event->relateduserid);

            $skillpoints = self::get_skill_points_data($skillsgrade, $event->get_grade());

            foreach ($skillpoints as $skillpointobject) {
                $userpoints->add_points($skillpointobject);
            }
        }
    }

    protected static function handle_evokeportfolio($event, $cm, $skillsgrade) {
        global $DB;

        $evokeportfolio = $DB->get_record('evokeportfolio', ['id' => $cm->instance]);

        if (!$evokeportfolio) {
            return;
        }

        $groupmembersids = [];
        if ($evokeportfolio->groupactivity) {
            $groupsutil = new \mod_evokeportfolio\util\group();

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
        }

        $userpoints = new point($event->courseid, $event->relateduserid);

        $skillpoints = self::get_skill_points_data($skillsgrade, $event->get_grade());

        foreach ($skillpoints as $skillpointobject) {
            $userpoints->add_points($skillpointobject);

            if (!$evokeportfolio->groupactivity && !$groupmembersids) {
                continue;
            }

            foreach ($groupmembersids as $groupmemberid) {
                $groupmemberpoints = new point($event->courseid, $groupmemberid);

                $groupmemberpoints->add_points($skillpointobject);

                unset($groupmemberpoints);
            }
        }
    }

    protected static function handle_portfoliobuilder_or_portfoliogroup($event, $cm, $skillsgrade) {
        $userpoints = new point($event->courseid, $event->relateduserid);

        $skillpoints = self::get_skill_points_data($skillsgrade, $event->get_grade());

        foreach ($skillpoints as $skillpointobject) {
            $userpoints->add_points($skillpointobject);
        }
    }

    protected static function get_skill_points_data($skillsgrade, $grade) {
        $skillpoints = [];

        foreach ($skillsgrade as $skill) {
            $pointstoadd = $skill->value;

            if ($grade->rawscaleid && (((int) $grade->rawgrademax) == 4)) {
                $multiplier = 1;
                if ((int)$grade->finalgrade == 1) {
                    $multiplier = 0.5;
                }
                if ((int)$grade->finalgrade == 2) {
                    $multiplier = 0.75;
                }
                if ((int)$grade->finalgrade == 3) {
                    $multiplier = 1;
                }
                if ((int)$grade->finalgrade == 4) {
                    $multiplier = 1.5;
                }

                $pointstoadd = ceil($pointstoadd * $multiplier);
            }

            $skillpoints[$skill->id] = [
                'id' => $skill->id,
                'skillmoduleid' => $skill->skillmoduleid,
                'pointstoadd' => $pointstoadd
            ];
        }

        return $skillpoints;
    }
}
