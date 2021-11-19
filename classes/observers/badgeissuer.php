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
use core_badges\badge;
use local_evokegame\util\badgecriteria;

class badgeissuer {
    public static function observer(baseevent $event) {
        global $DB;

        $evokebadges = $DB->get_records('evokegame_badges', ['courseid' => $event->courseid]);

        if (!$evokebadges) {
            return;
        }

        foreach ($evokebadges as $evokebadge) {
            if (self::user_already_have_badge($event->relateduserid, $evokebadge->badgeid)) {
                continue;
            }

            $badgecriterias = $DB->get_records('evokegame_badges_criterias', ['evokebadgeid' => $evokebadge->id]);

            if (!$badgecriterias) {
                continue;
            }

            if (self::check_if_user_can_receive_badge($event->relateduserid, $badgecriterias)) {
                self::deliver_badge($event->relateduserid, $evokebadge);
            }
        }
    }

    private static function user_already_have_badge($userid, $badgerid) {
        $badge = new \core_badges\badge($badgerid);

        return $badge->is_issued($userid);
    }

    private static function check_if_user_can_receive_badge($userid, $badgecriterias) {
        $badgecriteriautil = new badgecriteria();

        foreach ($badgecriterias as $badgecriteria) {
            $hascriteria = $badgecriteriautil->check_if_user_achieved_criteria($userid, $badgecriteria);

            if (!$hascriteria) {
                return false;
            }
        }

        return true;
    }

    private static function deliver_badge($userid, $evokebadge) {
        global $CFG;

        require_once($CFG->libdir . '/badgeslib.php');
        require_once($CFG->dirroot . '/badges/lib/awardlib.php');

        // Admin userid.
        $issuerid = 2;
        // Admin role.
        $issuerrole = 3;

        $badge = new \core_badges\badge($evokebadge->badgeid);

        $badgeadded = process_manual_award($userid, $issuerid, $issuerrole, $evokebadge->badgeid);

        if ($badgeadded) {
            $badge->issue($userid);

            $notification = new \local_evokegame\notification\badge($userid);

            $notification->notify($evokebadge->id);
        }
    }
}
