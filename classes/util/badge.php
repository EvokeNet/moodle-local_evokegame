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

class badge {
    public function __construct() {
        global $CFG;

        require_once($CFG->libdir . '/badgeslib.php');
    }

    public function get_course_badges_with_user_award($userid, $courseid) {
        global $CFG, $PAGE;

        $coursebadges = $this->get_course_badges($courseid);

        if (!$coursebadges) {
            return false;
        }

        require_once($CFG->libdir . '/badgeslib.php');

        $badges = [];
        foreach ($coursebadges as $coursebadge) {
            $badges[] = [
                'id' => $coursebadge->id,
                'name' => $coursebadge->name,
                'description' => $coursebadge->description,
                'badgeimage' => $this->get_badge_image_url($PAGE->context->id, $coursebadge->id),
                'awarded' => false
            ];
        }

        $userbadges = $this->get_user_course_badges($userid, $courseid);

        if (!$userbadges) {
            return $badges;
        }

        foreach ($badges as $key => $badge) {
            foreach ($userbadges as $userbadge) {
                if ($badge['id'] == $userbadge->id) {
                    $badges[$key]['awarded'] = true;
                    continue 2;
                }
            }
        }

        return $badges;
    }

    public function get_course_badges($courseid) {
        // Get badges fro badgelib.
        return badges_get_badges(BADGE_TYPE_COURSE, $courseid);
    }

    public function get_active_course_badges_select($courseid) {
        $badges = $this->get_course_badges($courseid);

        if(!$badges) {
            return false;
        }

        $data = [];
        foreach ($badges as $badge) {
            if ($badge->status == 1 || $badge->status == 3) {
                $data[$badge->id] = $badge->name;
            }
        }

        return $data;
    }

    public function get_user_course_badges($userid, $courseid) {
        // Get badges fro badgelib.
        $userbadges = badges_get_user_badges($userid, $courseid);

        if ($userbadges) {
            return $userbadges;
        }

        return false;
    }

    public function get_badge_image_url($contextid, $badgeid) {
        $imageurl = moodle_url::make_pluginfile_url($contextid, 'badges', 'badgeimage', $badgeid, '/', 'f1', false);

        $imageurl->param('refresh', rand(1, 10000));

        return $imageurl;
    }

    public function get_evoke_badges($courseid) {
        global $DB;

        $records = $DB->get_records('evokegame_badges', ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }
}
