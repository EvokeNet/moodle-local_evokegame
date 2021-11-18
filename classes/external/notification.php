<?php

namespace local_evokegame\external;

use context;
use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use local_evokegame\forms\badge as badgeform;

/**
 * Notification external api class.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class notification extends external_api {
    /**
     * Check badge notification parameters
     *
     * @return external_function_parameters
     */
    public static function checknotificationbadge_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Check badge notification method
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function checknotificationbadge() {
        global $USER, $PAGE, $DB;

        $notification = new \local_evokegame\notification\badge($USER->id);

        if (!$notification->should_be_notified()) {
            return [
                'status' => false,
                'badgename' => null,
                'courseid' => null,
                'badgeimage' => null
            ];
        }

        $badgeid = $notification->get_notification_data();

        $evokebadge = $DB->get_record('evokegame_badges', ['id' => $badgeid], '*', MUST_EXIST);

        $context = \context_course::instance($evokebadge->courseid);
        $PAGE->set_context($context);

        $notification->mark_as_notified();

        $badgeutil = new \local_evokegame\util\badge();
        $badgeimage = $badgeutil->get_badge_image_url($context->id, $evokebadge->badgeid);

        return [
            'status' => true,
            'badgename' => $evokebadge->name,
            'courseid' => $evokebadge->courseid,
            'badgeimage' => $badgeimage->out(),
        ];
    }

    /**
     * Check badge notification return fields
     *
     * @return external_single_structure
     */
    public static function checknotificationbadge_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'Operation status'),
                'badgename' => new external_value(PARAM_TEXT, 'Badge name'),
                'courseid' => new external_value(PARAM_INT, 'Badge course id'),
                'badgeimage' => new external_value(PARAM_RAW, 'Badge image'),
            )
        );
    }
}