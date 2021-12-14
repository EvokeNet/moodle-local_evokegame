<?php

namespace local_evokegame\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

use local_evokegame\util\badge;
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

        $this->no_sorting('superpowers');

        $this->define_baseurl(new moodle_url('/local/evokegame/scoreboard.php', ['id' => $this->course->id]));

        $this->base_sql();

        $this->set_attribute('class', 'table table-bordered table-scoreboard');
    }

    public function base_sql() {
        $fields = 'DISTINCT u.id, u.firstname, u.lastname, u.email, e.coins as points';

        $capjoin = get_enrolled_with_capabilities_join($this->context, '', 'moodle/course:viewparticipants');

        $from = ' {user} u ' . $capjoin->joins;

        $from .= ' INNER JOIN {evokegame_evcs} e ON e.userid = u.id ';

        $params = $capjoin->params;

        $where = $capjoin->wheres;

        $where .= ' ORDER BY e.coins DESC';

        $this->set_sql($fields, $from, $where, $params);
    }

    public function col_fullname($user) {
        return $user->firstname . ' ' . $user->lastname;
    }

    public function col_myteams($user) {
        $userutil = new user();

        $usergroups = $userutil->get_user_course_groups($this->course->id, $user->id);

        if (!$usergroups) {
            return '';
        }

        $output = '';
        foreach ($usergroups as $usergroup) {
            $output .= html_writer::tag('span', $usergroup, ['class' => 'badge badge-info']);
        }

        return $output;
    }

    public function col_superpowers($user) {
        $badgeutil = new badge();

        $userbadges = $badgeutil->get_course_badges_with_user_award($user->id, $this->course->id, $this->context->id);
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
        return ['id', 'fullname', 'myteams', 'superpowers', 'points'];
    }

    private function get_headers() {
        return [
            'ID',
            get_string('fullname'),
            get_string('myteams', 'local_evokegame'),
            get_string('collectedsuperpowers', 'local_evokegame'),
            get_string('points', 'local_evokegame')
        ];
    }
}