<?php

namespace local_evokegame\util;

class game {
    public static function is_enabled_in_course($courseid) {
        $isgameenabledincourse = get_config('local_evokegame', 'isgameenabledincourse-' . $courseid);

        if (is_null($isgameenabledincourse) || $isgameenabledincourse == 1) {
            return true;
        }

        return false;
    }
}