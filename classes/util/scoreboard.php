<?php

namespace local_evokegame\util;

class scoreboard {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function get_scoreboard($limitfrom = 0, $limitnum = 10) {
        global $DB;

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'moodle/course:viewparticipants');

        $sql = "SELECT DISTINCT u.*, evc.coins
                FROM {user} u
                $capjoin->joins
                INNER JOIN {evokegame_evcs} evc ON u.id = evc.userid
                ORDER BY evc.coins DESC";

        $params = $capjoin->params;

        $records = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

        if (!$records) {
            return false;
        }

        $records = array_values($records);

        $this->fill_with_userinfo($records);

        $this->fill_with_position($records);

        $this->fill_with_powers($records);

        return $records;
    }

    private function fill_with_userinfo($data) {
        global $PAGE;

        foreach ($data as $user) {
            $userpicture = new \user_picture($user);

            $user->fullname = fullname($user);

            $user->userpicture = $userpicture->get_url($PAGE);

            $user->coins = (int) $user->coins;

            $badgeutil = new badge();

            $user->userbadges = $badgeutil->get_course_badges_with_user_award($user->id, $this->course->id, $this->context->id);
        }
    }

    /**
     * Get the users position data.
     *
     * @param array $data
     *
     * @return string|array
     *
     * @throws \coding_exception
     */
    protected function fill_with_position($data) {
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]->position = $i + 1;
        }
    }

    protected function fill_with_powers($data) {
        $skillutil = new skill();

        foreach ($data as $user) {
            $skills = $skillutil->get_course_skills_set($this->course->id, $user->id);

            $user->powers = 0;
            if (!$skills) {
                continue;
            }

            $totalpoints = 0;
            $userpoints = 0;
            foreach ($skills as $skill) {
                $totalpoints += $skill['totalpoints'];
                $userpoints += $skill['points'];
            }

            if ($userpoints != 0) {
                $user->powers = (int)(($userpoints * 100) / $totalpoints);
            }
        }
    }
}