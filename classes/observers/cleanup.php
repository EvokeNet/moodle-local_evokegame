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

class cleanup {
    public static function moduledeleted(baseevent $event) {
        if (!game::is_enabled_in_course($event->courseid)) {
            return;
        }

        $cleanup = new \local_evokegame\util\cleanup();

        $cleanup->delete_activity_coins($event->objectid);

        $cleanup->delete_activity_skills($event->objectid);
    }
}
