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
        global $USER, $PAGE, $CFG;

        if (!$user) {
            $user = $USER;
        }

        $useravatar = get_user_preferences('evokegame_avatarid', null, $user);

        if ($useravatar) {
            return $CFG->wwwroot . '/local/evokegame/pix/a' . $useravatar . '.svg';
        }

        $userpicture = new \user_picture($user);
        $userpicture->size = 1;

        return $userpicture->get_url($PAGE);
    }
}
