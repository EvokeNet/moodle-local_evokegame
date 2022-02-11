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
use local_evokegame\util\point;

class usergraded {
    public static function observer(baseevent $event) {
        $handler = extrafieldshandler::create();

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        $gradeitemid = $event->other['itemid'];

        $gradeitem = self::get_grade_item($gradeitemid);

        if (!$gradeitem || $gradeitem->itemtype != 'mod') {
            return;
        }

        $cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance);

        if (!$cm) {
            return;
        }

        $data = $handler->export_instance_data_object($cm->id);

        if (!preg_grep('/^grading_/', array_keys((array)$data))) {
            // For performance.
            return;
        }

        $points = new point($event->courseid, $event->relateduserid);

        foreach ($data as $skill => $value) {
            if (!$value || empty($value) || $value == 0) {
                continue;
            }

            if (substr($skill, 0, 8) != 'grading_') {
                continue;
            }

            // String submission_ length == 8.
            $submissionskill = substr($skill, 8);

//            $pointstoadd = $event->get_grade()->finalgrade * $value;
            $pointstoadd = $value;

            $points->add_points('module', 'grading', $cm->id, $submissionskill, $pointstoadd);
        }
    }

    protected static function get_grade_item($itemid) {
        global $DB;

        return $DB->get_record('grade_items', ['id' => $itemid]);
    }
}
