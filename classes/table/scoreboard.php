<?php

namespace local_evokegame\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

use local_evokegame\util\badge;
use local_evokegame\util\skill;
use local_evokegame\util\user;
use table_sql;
use moodle_url;
use html_writer;

/**
 * Entries table class
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class scoreboard extends table_sql {
    protected $context;
    protected $course;

    public function __construct($uniqueid, $context, $course) {
        parent::__construct($uniqueid);

        $this->context = $context;
        $this->course = $course;

        $this->define_columns($this->get_columns());

        $this->define_headers($this->get_headers());

        $this->no_sorting('agent');
        $this->no_sorting('evc');
        $this->no_sorting('powers');

        $this->define_baseurl(new moodle_url('/local/evokegame/scoreboardall.php', ['id' => $this->course->id]));

        $this->base_sql();

        $this->set_attribute('class', 'table table-bordered table-scoreboard');
    }

    public function base_sql() {
        $fields = 'DISTINCT u.id, u.firstname, u.lastname, u.email, e.coins as evc, p.points, (e.coins + p.points) as score';

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'moodle/course:viewparticipants');

        $from = ' {user} u ' . $capjoin->joins;

        $from .= ' INNER JOIN {evokegame_evcs} e ON e.userid = u.id
                   LEFT JOIN {evokegame_points} p ON u.id = p.userid AND p.courseid = :courseid ';

        $params = $capjoin->params;

        $params['courseid'] = $this->course->id;

        $where = $capjoin->wheres;

        $this->set_sql($fields, $from, $where, $params);
    }

    public function get_sql_sort() {
        return 'score DESC, evc DESC, firstname ASC';
    }

    public function col_agent($user) {
        return html_writer::link(new moodle_url('/local/evokegame/profile.php', ['id' => $this->course->id, 'userid' => $user->id]), $user->firstname . ' ' . $user->lastname);
    }

    public function col_powers($user) {
        $skillutil = new skill();

        $skills = $skillutil->get_course_skills_set($this->course->id, $user->id);

        $user->powers = 0;
        if (!$skills) {
            return '0%';
        }

        $totalpoints = 0;
        $userpoints = 0;
        foreach ($skills as $skill) {
            $totalpoints += $skill['totalpoints'];
            $userpoints += $skill['points'];
        }

        if ($userpoints != 0) {
            return (int)(($userpoints * 100) / $totalpoints) . '%';
        }

        return '-';
    }

    private function get_columns() {
        return ['agent', 'evc', 'powers'];
    }

    private function get_headers() {
        return [
            get_string('scoreboard_agent', 'local_evokegame'),
            get_string('scoreboard_evc', 'local_evokegame'),
            get_string('scoreboard_powers', 'local_evokegame')
        ];
    }
}