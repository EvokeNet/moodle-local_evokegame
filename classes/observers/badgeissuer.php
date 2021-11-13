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

class badgeissuer {
    public static function observer(baseevent $event) {
        global $CFG;

        require_once($CFG->libdir . '/badgeslib.php');
        require_once($CFG->dirroot . '/badges/lib/awardlib.php');

        // Admin userid.
        $issuerid = 2;
        // Admin role.
        $issuerrole = 3;

        $badge = new \core_badges\badge($event->badgeid);

        process_manual_award($event->relateduserid, $issuerid, $issuerrole, $event->badgeid);

        $badge->issue($event->relateduserid);
    }
}
