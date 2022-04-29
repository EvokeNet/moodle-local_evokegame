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

        $sql = 'SELECT u.*, p.points
                FROM {evokegame_points} p
                INNER JOIN {user} u ON u.id = p.userid
                WHERE p.courseid = :courseid
                ORDER BY p.points DESC';

        $records = $DB->get_records_sql($sql, ['courseid' => $this->course->id], $limitfrom, $limitnum);

        if (!$records) {
            return false;
        }

        $records = array_values($records);

        $this->fill_with_userinfo($records);

        $this->fill_with_position($records);

        return $records;
    }

    private function fill_with_userinfo($data) {
        global $PAGE;

        foreach ($data as $user) {
            $userpicture = new \user_picture($user);

            $user->fullname = fullname($user);

            $user->userpicture = $userpicture->get_url($PAGE);

            $user->points = (int) $user->points;

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
        $lastpos = 1;
        $lastpoints = current($data)->points;
        for ($i = 0; $i < count($data); $i++) {
            if ($lastpoints > $data[$i]->points) {
                $lastpos++;
                $lastpoints = $data[$i]->points;
            }

            $data[$i]->position = $lastpos;
        }
    }
}