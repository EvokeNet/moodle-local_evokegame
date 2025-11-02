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
use local_evokegame\util\evocoin;
use local_evokegame\util\evocoinmodule;
use local_evokegame\util\game;

class modulecompleted {
    public static function observer(baseevent $event) {
        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        if (!self::is_completion_completed($event->objectid)) {
            return;
        }

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $educoinmoduleutil = new evocoinmodule();
        if (!$modulecoins = $educoinmoduleutil->get_module_coins($event->contextinstanceid)) {
            return;
        }

        $evcs = new evocoin($event->relateduserid);
        // First we log transaction, because this function checks if the point was added in the past.
        // This event if fired more than one time, we need to prevent add points much times.
        $pointsadded = $evcs->log_transaction(
            $event->courseid,
            'module',
            $event->target,
            $event->contextinstanceid,
            $modulecoins,
            'in'
        );

        if ($pointsadded) {
            $evcs->add_coins($modulecoins, $event->courseid);
        }
    }

    /**
     * Verify if the completion is completed
     *
     * @param int $cmcid
     *
     * @return boolean
     */
    protected static function is_completion_completed($cmcid) {
        global $DB;

        $cmc = $DB->get_record('course_modules_completion', ['id' => $cmcid], '*');

        if ($cmc) {
            return (bool) $cmc->completionstate;
        }

        return false;
    }
}
