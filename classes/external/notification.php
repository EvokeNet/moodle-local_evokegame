<?php

namespace local_evokegame\external;

use core\context\course as context_course;
use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;

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
                'isachievement' => false,
                'badgename' => '',
                'courseid' => 0,
                'badgeimage' => '',
                'title' => '',
                'description' => '',
                'buttontext' => '',
                'closetext' => '',
            ];
        }

        $badgeid = $notification->get_notification_data();

        $evokebadge = $DB->get_record('evokegame_badges', ['id' => $badgeid], '*', MUST_EXIST);

        $context = context_course::instance($evokebadge->courseid);
        $PAGE->set_context($context);

        $notification->mark_as_notified();

        $badgeutil = new \local_evokegame\util\badge();
        $badgeimage = $badgeutil->get_badge_image_url($context->id, $evokebadge->badgeid);

        $isachievement = $evokebadge->type == 2;
        
        // Get translated strings
        $title = $isachievement 
            ? get_string('youveearnedanachievement', 'local_evokegame')
            : get_string('youveearnedabadge', 'local_evokegame');
        
        $description = $isachievement
            ? get_string('youjustearnedanewachievement_desc', 'local_evokegame', $evokebadge->name)
            : get_string('youjustearnedanewbadge_desc', 'local_evokegame', $evokebadge->name);
        
        $buttontext = get_string('checkyourscoreboard', 'local_evokegame');
        $closetext = get_string('closebuttontitle');

        return [
            'status' => true,
            'isachievement' => $isachievement,
            'badgename' => $evokebadge->name,
            'courseid' => $evokebadge->courseid,
            'badgeimage' => $badgeimage->out(),
            'title' => $title,
            'description' => $description,
            'buttontext' => $buttontext,
            'closetext' => $closetext,
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
                'isachievement' => new external_value(PARAM_BOOL, 'Is achievement or badge'),
                'badgename' => new external_value(PARAM_TEXT, 'Badge name'),
                'courseid' => new external_value(PARAM_INT, 'Badge course id'),
                'badgeimage' => new external_value(PARAM_RAW, 'Badge image'),
                'title' => new external_value(PARAM_RAW, 'Modal title'),
                'description' => new external_value(PARAM_RAW, 'Modal description'),
                'buttontext' => new external_value(PARAM_TEXT, 'Button text'),
                'closetext' => new external_value(PARAM_TEXT, 'Close button text'),
            )
        );
    }
}
