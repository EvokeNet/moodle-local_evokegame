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

    public function get_awarded_course_badges($userid, $courseid, $contextid, $type = 1, $highlight = null) {
        $badges = $this->get_course_badges_with_user_award($userid, $courseid, $contextid, $type, $highlight);

        if (!$badges) {
            return false;
        }

        $mybadges = [];
        foreach ($badges as $badge) {
            if ($badge['awarded']) {
                $mybadges[] = $badge;
            }
        }

        return $mybadges;
    }

    public function get_awarded_course_achievements($userid, $courseid, $contextid) {
        $badgetype = 2;

        $badges = $this->get_course_badges_with_user_award($userid, $courseid, $contextid, $badgetype);

        if (!$badges) {
            return false;
        }

        $myachievements = [];
        foreach ($badges as $badge) {
            if ($badge['awarded']) {
                $myachievements[] = $badge;
            }
        }

        return $myachievements;
    }

    public function get_course_badges_with_user_award($userid, $courseid, $contextid, $type = 1, $highlight = null) {
        $coursebadges = $this->get_course_badges($courseid, $type, $highlight);

        if (!$coursebadges) {
            return false;
        }

        $badges = [];
        foreach ($coursebadges as $coursebadge) {
            $badges[] = [
                'id' => $coursebadge->id,
                'badgeid' => $coursebadge->badgeid,
                'name' => $coursebadge->name,
                'description' => $coursebadge->description,
                'badgeimage' => $this->get_badge_image_url($contextid, $coursebadge->badgeid),
                'awarded' => false
            ];
        }

        $userbadges = $this->get_user_course_badges($userid, $courseid);

        if (!$userbadges) {
            return $badges;
        }

        foreach ($badges as $key => $badge) {
            foreach ($userbadges as $userbadge) {
                if ($badge['badgeid'] == $userbadge->id) {
                    $badges[$key]['awarded'] = true;
                    continue 2;
                }
            }
        }

        return $badges;
    }

    public function get_course_highlight_badges_with_user_award($userid, $courseid, $contextid) {
        $coursebadges = $this->get_course_badges($courseid, 1, 1);

        if (!$coursebadges) {
            return false;
        }

        $badges = [];
        foreach ($coursebadges as $coursebadge) {
            $badges[] = [
                'id' => $coursebadge->id,
                'badgeid' => $coursebadge->badgeid,
                'name' => $coursebadge->name,
                'description' => $coursebadge->description,
                'badgeimage' => $this->get_badge_image_url($contextid, $coursebadge->badgeid),
                'awarded' => false
            ];
        }

        $userbadges = $this->get_user_course_badges($userid, $courseid);

        if (!$userbadges) {
            return $badges;
        }

        foreach ($badges as $key => $badge) {
            foreach ($userbadges as $userbadge) {
                if ($badge['badgeid'] == $userbadge->id) {
                    $badges[$key]['awarded'] = true;
                    continue 2;
                }
            }
        }

        return $badges;
    }

    public function get_course_badges($courseid, $type = 1, $highlight = null) {
        global $DB;

        $sql = 'SELECT eb.*, b.description
                FROM {evokegame_badges} eb
                INNER JOIN {badge} b ON b.id = eb.badgeid
                WHERE b.courseid = :courseid AND eb.type = :type';

        $params = ['courseid' => $courseid, 'type' => $type];

        if ($highlight !== null) {
            $sql .= ' AND eb.highlight = :highlight';
            $params['highlight'] = $highlight;
        }

        $records = $DB->get_records_sql($sql, $params);

        if (!$records) {
            return false;
        }

        return array_values($records);
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

    public function get_user_course_badges_with_criterias($userid, $courseid, $contextid, $highlight = null) {
        $badgecriteria = new badgecriteria();

        $badges = $this->get_course_badges_with_user_award($userid, $courseid, $contextid, 1, $highlight);

        foreach ($badges as $key => $badge) {
            $criterias = $badgecriteria->get_evoke_badge_criterias($badge['id']);

            if (!$criterias) {
                unset($badges[$key]);

                continue;
            }

            $criteriasachieved = 0;
            foreach ($criterias as $criteria) {
                if ($badgecriteria->check_if_user_achieved_criteria($userid, $criteria)) {
                    $criteriasachieved++;
                }
            }

            $badges[$key]['totalcriterias'] = count($criterias);
            $badges[$key]['totalachieved'] = $criteriasachieved;
            $badges[$key]['progress'] = 0;

            if ($criteriasachieved > 0) {
                $badges[$key]['progress'] = (int)($criteriasachieved * 100 / $badges[$key]['totalcriterias']);
            }
        }

        return array_values($badges);
    }
}
