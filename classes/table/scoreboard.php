<?php

namespace local_evokegame\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

use local_evokegame\util\badge;
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

        $this->no_sorting('superpowers');

        $this->define_baseurl(new moodle_url('/local/evokegame/scoreboard.php', ['id' => $this->course->id]));

        $this->base_sql();

        $this->set_attribute('class', 'table table-bordered table-scoreboard');
    }

    public function base_sql() {
        $fields = 'DISTINCT u.id, u.firstname, u.lastname, u.email, p.points';

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'moodle/course:viewparticipants');

        $from = ' {user} u ' . $capjoin->joins;

        $from .= ' INNER JOIN {evokegame_points} p ON p.userid = u.id AND p.courseid = :courseid ';

        $params = $capjoin->params;
        $params['courseid'] = $this->course->id;

        $this->set_sql($fields, $from, $capjoin->wheres, $params);
    }

    public function col_fullname($user) {
        return $user->firstname . ' ' . $user->lastname;
    }

    public function col_superpowers($user) {
        $badgeutil = new badge();

        $userbadges = $badgeutil->get_course_badges_with_user_award($user->id, $this->course->id);
        $userbadgescolumncontent = '';
        if ($userbadges) {
            foreach ($userbadges as $userbadge) {
                $badgeclasses = 'evokebadge';
                if (!$userbadge['awarded']) {
                    $badgeclasses .= ' dimmed';
                }
                $userbadgescolumncontent .= '<img src="'.$userbadge['badgeimage'].'" alt="'.$userbadge['name'].'" class="'.$badgeclasses.'">';
            }
        }

        return $userbadgescolumncontent;
    }

    private function get_columns() {
        return ['id', 'fullname', 'email', 'superpowers', 'points'];
    }

    private function get_headers() {
        return [
            'ID',
            get_string('fullname'),
            'E-mail',
            get_string('collectedsuperpowers', 'local_evokegame'),
            get_string('points', 'local_evokegame')
        ];
    }
}