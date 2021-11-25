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
use local_evokegame\customfield\mod_handler as extrafieldshandler;
use local_evokegame\util\evocoin;

class modulecompleted {
    public static function observer(baseevent $event) {
        if (!self::is_completion_completed($event->objectid)) {
            return;
        }

        $handler = extrafieldshandler::create();

        $cmid = $event->contextinstanceid;

        $data = $handler->export_instance_data_object($cmid);

        $customfields = (array)$data;

        if (!$customfields) {
            return;
        }

        if (!array_key_exists('evocoins', $customfields)) {
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
            $customfields['evocoins'],
            'in'
        );

        if ($pointsadded) {
            $evcs->add_coins($customfields['evocoins']);
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
