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

        $sql = "SELECT DISTINCT u.*, evc.coins, p.points, (evc.coins + p.points) as score
                FROM {user} u
                $capjoin->joins
                INNER JOIN {evokegame_evcs} evc ON u.id = evc.userid
                LEFT JOIN {evokegame_points} p ON u.id = p.userid AND p.courseid = :courseid
                ORDER BY score DESC, evc.coins DESC, u.firstname ASC";

        $params = $capjoin->params;

        $params['courseid'] = $this->course->id;

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

            $user->userbadges = $badgeutil->get_course_highlight_badges_with_user_award($user->id, $this->course->id, $this->context->id);
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
                $totalpoints += $skill['points'];
                $userpoints += $skill['userpoints'];
            }

            if ($userpoints != 0) {
                $user->powers = (int)(($userpoints * 100) / $totalpoints);
            }
        }
    }

    /**
     * @return mixed
     */
    public function get_by_skill_and_evc_date($datestart = null, $dateend = null, $limit = 10) {
        global $DB;

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'moodle/course:viewparticipants');

        $skillsjoin = $this->get_scoreboard_by_skills_query($datestart, $dateend);
        $evocoinsjoin = $this->get_scoreboard_by_evocoins_query($datestart, $dateend);

        $sql = "SELECT DISTINCT u.*, skills.points, coins.evcs, (IFNULL(skills.points, 0) + IFNULL(coins.evcs, 0)) as score
                FROM {user} u
                {$capjoin->joins}
                LEFT JOIN ({$skillsjoin->query}) skills ON (skills.userid = u.id AND skills.course = :courseid1)
                LEFT JOIN ({$evocoinsjoin->query}) coins ON (coins.userid = u.id AND coins.courseid = :courseid2)
                HAVING score > 0
                ORDER BY score DESC
                LIMIT 10";

        $params = [
            'courseid1' => $this->course->id,
            'courseid2' => $this->course->id,
            'limit' => $limit
        ];

        $params = array_merge($params, $capjoin->params, $skillsjoin->params, $evocoinsjoin->params);

        $records = $DB->get_records_sql($sql, $params);

        if (!$records) {
            return [];
        }

        return array_values($records);
    }

    private function get_scoreboard_by_skills_query($datestart = null, $dateend = null) {
        $data = new \stdClass();

        if (!$datestart || !$dateend) {
            $sql = 'SELECT sku.userid, cm.course, SUM(sku.value) as points
                FROM {evokegame_skills_users} sku
                INNER JOIN {evokegame_skills_modules} skm ON skm.id = sku.skillmoduleid
                INNER JOIN {course_modules} cm ON cm.id = skm.cmid
                GROUP BY userid, course';

            $data->query = $sql;
            $data->params = [];

            return $data;
        }

        $sql = 'SELECT sku.userid, cm.course, SUM(sku.value) as points
                FROM {evokegame_skills_users} sku
                INNER JOIN {evokegame_skills_modules} skm ON skm.id = sku.skillmoduleid
                INNER JOIN {course_modules} cm ON cm.id = skm.cmid
                WHERE sku.timecreated BETWEEN :skillsdatestart AND :skillsdateend
                GROUP BY userid, course';

        $data->query = $sql;
        $data->params = [
            'skillsdatestart' => $datestart,
            'skillsdateend' => $dateend
        ];

        return $data;
    }

    private function get_scoreboard_by_evocoins_query($datestart = null, $dateend = null) {
        $data = new \stdClass();

        if (!$datestart || !$dateend) {
            $sql = "SELECT userid, courseid, SUM(coins) as evcs
                    FROM {evokegame_evcs_transactions}
                    WHERE action = 'in'
                    GROUP BY userid, courseid";

            $data->query = $sql;
            $data->params = [];

            return $data;
        }

        $sql = "SELECT userid, courseid, SUM(coins) as evcs
                FROM {evokegame_evcs_transactions}
                WHERE action = 'in' AND timecreated BETWEEN :evocoinsdatestart AND :evocoinsdateend
                GROUP BY userid, courseid";

        $data->query = $sql;
        $data->params = [
            'evocoinsdatestart' => $datestart,
            'evocoinsdateend' => $dateend
        ];

        return $data;
    }
}
