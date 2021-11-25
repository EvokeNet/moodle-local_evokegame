<?php

/**
 * Badges util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

use moodle_url;

class evocoin {
    protected $user;

    public function __construct($user = null) {
        global $USER;

        $this->user = $user;

        if (!$user) {
            $this->user = $USER;
        }
    }

    public function add_coins($coins) {
        global $DB;

        $evcrecord = $this->get_evcs_record();

        // Add evocoins to the evcs table.
        $evcrecord->coins += $coins;
        $evcrecord->timemodified = time();

        $DB->update_record('evokegame_evcs', $evcrecord);
    }

    public function log_transaction($courseid, $source, $sourcetype, $sourceid, $coins, $action) {
        global $DB;

        if ($this->in_transaction_exists($courseid, $source, $sourcetype, $sourceid)) {
            return false;
        }

        $data = new \stdClass();
        $data->courseid = $courseid;
        $data->userid = $this->user->id;
        $data->source = $source;
        $data->sourcetype = $sourcetype;
        $data->sourceid = $sourceid;
        $data->coins = $coins;
        $data->action = $action;
        $data->timecreated = time();

        return $DB->insert_record('evokegame_evcs_transactions', $data);
    }

    public function in_transaction_exists($courseid, $source, $sourcetype, $sourceid) {
        global $DB;

        $record = $DB->get_record('evokegame_evcs_transactions', [
            'courseid' => $courseid,
            'userid' => $this->user->id,
            'source' => $source,
            'sourcetype' => $sourcetype,
            'sourceid' => $sourceid,
            'action' => 'in'
        ]);

        if ($record) {
            return true;
        }

        return false;
    }

    public function get_coins() {
        $evcrecord = $this->get_evcs_record();

        return $evcrecord->coins;
    }

    public function get_evcs_record() {
        global $DB;

        $record = $DB->get_record('evokegame_evcs', ['userid' => $this->user->id]);

        if ($record) {
            return $record;
        }

        return $this->insert_evc_record();
    }

    private function insert_evc_record() {
        global $DB;

        $time = time();

        $evocoin = new \stdClass();
        $evocoin->userid = $this->user->id;
        $evocoin->coins = 0;
        $evocoin->timecreated = $time;
        $evocoin->timemodified = $time;

        $id = $DB->insert_record('evokegame_evcs', $evocoin);

        $evocoin->id = $id;

        return $evocoin;
    }
}