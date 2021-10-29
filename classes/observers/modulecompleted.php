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

class modulecompleted {
    public static function observer(baseevent $event) {
        $handler = extrafieldshandler::create();

        // TODO: Get course module id from event.
        $data = $handler->export_instance_data_object($event->cmid);
    }
}
