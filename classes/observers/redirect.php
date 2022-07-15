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

class redirect {
    public static function observer(baseevent $event) {
        global $USER;

        if (is_siteadmin()) {
            return;
        }

        if ($event instanceof \core\event\course_viewed) {
            $data = $event->get_data();

            if ($data['courseid'] > 1) {
                return;
            }
        }

        $courses = enrol_get_all_users_courses($USER->id);

        if (empty($courses) ||count($courses) > 1) {
            return;
        }

        $course = current($courses);

        redirect(new \moodle_url('/course/view.php', ['id' => $course->id]));
    }
}
