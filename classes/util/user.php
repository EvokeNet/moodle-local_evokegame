<?php

/**
 * User util class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\util;

defined('MOODLE_INTERNAL') || die;

class user {
    public function get_user_avatar_or_image($user = null) {
        global $USER, $PAGE, $CFG, $DB;

        if (!$user) {
            $user = $USER;
        }

        if (!is_object($user)) {
            $user = $DB->get_record('user', ['id' => $user], '*', MUST_EXIST);
        }

        $useravatar = get_user_preferences('evokegame_avatarid', null, $user);

        if ($useravatar) {
            return $CFG->wwwroot . '/local/evokegame/pix/a' . $useravatar . '.svg';
        }

        $userpicture = new \user_picture($user);
        $userpicture->size = 1;

        return $userpicture->get_url($PAGE);
    }

    public function get_user_course_groups($courseid, $userid) {
        global $DB;

        $sql = "SELECT g.*
                FROM {groups} g
                INNER JOIN {groups_members} gm ON gm.groupid = g.id
                WHERE g.courseid = :courseid AND gm.userid = :userid";

        $groupmembers = $DB->get_records_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

        if (!$groupmembers) {
            return false;
        }

        $groups = [];
        foreach ($groupmembers as $groupmember) {
            $groups[] = $groupmember->name;
        }

        return $groups;
    }
}
