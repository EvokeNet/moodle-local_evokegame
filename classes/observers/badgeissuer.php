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

class badgeissuer {
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

        $userid = $event->relateduserid;

        if (get_class($event) === 'core\event\course_viewed') {
            $userid = $event->userid;
        }

        $evokebadges = $DB->get_records('evokegame_badges', ['courseid' => $event->courseid]);

        if (!$evokebadges) {
            return;
        }

        foreach ($evokebadges as $evokebadge) {
            if (self::user_already_have_badge($userid, $evokebadge->badgeid)) {
                continue;
            }

            $badgecriterias = $DB->get_records('evokegame_badges_criterias', ['evokebadgeid' => $evokebadge->id]);

            if (!$badgecriterias) {
                continue;
            }

            if (self::check_if_user_can_receive_badge($userid, $badgecriterias)) {
                self::deliver_badge($userid, $evokebadge);
            }
        }
    }

    public static function user_already_have_badge($userid, $badgerid) {
        $badge = new \core_badges\badge($badgerid);

        return $badge->is_issued($userid);
    }

    public static function check_if_user_can_receive_badge($userid, $badgecriterias): bool {
        foreach ($badgecriterias as $badgecriteria) {
            $criteriaclass = '\evokegamebadgecriteria_' . $badgecriteria->method . '\badgecriteria';

            if (!class_exists($criteriaclass)) {
                return false;
            }

            $criteria = new $criteriaclass($userid, $badgecriteria);

            if (!$criteria->user_achieved_criteria()) {
                return false;
            }
        }

        return true;
    }

    public static function deliver_badge($userid, $evokebadge) {
        global $CFG;

        require_once($CFG->libdir . '/badgeslib.php');
        require_once($CFG->dirroot . '/badges/lib/awardlib.php');

        // Admin userid.
        $issuerid = 2;
        // Admin role.
        $issuerrole = 3;

        $badge = new \core_badges\badge($evokebadge->badgeid);

        // Check if badge was already issued to avoid duplicate notifications
        if ($badge->is_issued($userid)) {
            return;
        }

        $badgeadded = process_manual_award($userid, $issuerid, $issuerrole, $evokebadge->badgeid);

        // Issue the badge (process_manual_award might return false even when it works)
        try {
            $badge->issue($userid);
        } catch (\Exception $e) {
            // If badge was already issued, that's okay - just continue
            if (!$badge->is_issued($userid)) {
                throw $e;
            }
        }

        // Set notification regardless of process_manual_award return value if badge was successfully issued
        if ($badge->is_issued($userid)) {
            $notification = new \local_evokegame\notification\badge($userid);
            $notification->notify($evokebadge->id);
        }
    }
}
