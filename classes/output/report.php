<?php

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class report implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        $evocoins = new \local_evokegame\util\report\evocoins();
        $portfolio = new \local_evokegame\util\report\portfolio();
        $skills = new \local_evokegame\util\report\skills();
        $students = new \local_evokegame\util\report\students();
        $courseid = $this->course->id;
        $context = $this->context;

        $totalevocoins = $evocoins->get_course_total($courseid);

        $totalstudents = $students->get_course_total($context);

        $totalpossiblecoins = $totalevocoins * $totalstudents;

        $totaldistributedevocoins = $evocoins->get_course_total_distributed($courseid);

        $courseskills = $skills->get_course_skills_with_totalpoints($courseid);
        if (!$courseskills || !is_array($courseskills)) {
            $courseskills = [];
        }

        $totalskillspoints = array_reduce($courseskills, function($carry, $item) {
            $carry += $item->value;

            return $carry;
        }, 0);

        if ($totalpossiblecoins > 0) {
            $evocoinsdistributionprogress = (int)(ceil($totaldistributedevocoins * 100 / $totalpossiblecoins));
        } else {
            $evocoinsdistributionprogress = 0;
        }

        $portfoliochart = new \local_evokegame\util\report\chart\portfolio();

        $entriesbychapter = $portfoliochart->entries_by_chapter($courseid);
        if ($entriesbychapter) {
            $entriesbychapter = $output->render($entriesbychapter);
        }

        $likesbychapter = $portfoliochart->likes_by_chapter($courseid);
        if ($likesbychapter) {
            $likesbychapter = $output->render($likesbychapter);
        }

        $commentsbychapter = $portfoliochart->comments_by_chapter($courseid);
        if ($commentsbychapter) {
            $commentsbychapter = $output->render($commentsbychapter);
        }

        $studentslist = $this->build_students_report($courseid, $context);

        return [
            'courseevocoins' => $totalevocoins,
            'totalstudents' => $totalstudents,
            'totalpossiblecoins' => $totalpossiblecoins,
            'totalskillspoints' => $totalskillspoints,
            'totaldistributedevocoins' => $totaldistributedevocoins,
            'evocoinsdistributionprogress' => $evocoinsdistributionprogress,
            'courseskills' => $courseskills,
            'totalportfolioentries' => $portfolio->get_course_total_entries($this->course->id),
            'totalportfoliolikes' => $portfolio->get_course_total_likes($this->course->id),
            'totalportfoliocomments' => $portfolio->get_course_total_comments($this->course->id),
            'chartentriesbychapter' => $entriesbychapter,
            'chartlikesbychapter' => $likesbychapter,
            'chartcommentsbychapter' => $commentsbychapter,
            'studentsreport' => $studentslist
        ];
    }

    private function build_students_report(int $courseid, \context_course $context): array {
        global $DB;

        $enrolled = get_enrolled_users($context, 'moodle/course:viewparticipants', 0, 'u.id,u.firstname,u.lastname,u.email');
        if (!$enrolled) {
            return [];
        }

        $useridlist = array_keys($enrolled);
        list($insql, $params) = $DB->get_in_or_equal($useridlist, SQL_PARAMS_NAMED);
        $params['courseid'] = $courseid;

        $skillsmap = [];
        $skillssql = "SELECT su.userid, s.name, SUM(su.value) AS points
                        FROM {evokegame_skills_users} su
                        JOIN {evokegame_skills_modules} sm ON sm.id = su.skillmoduleid
                        JOIN {evokegame_skills} s ON s.id = sm.skillid
                       WHERE s.courseid = :courseid
                         AND su.userid {$insql}";
        $skillssql .= " GROUP BY su.userid, s.name";
        $skillrecords = $DB->get_records_sql($skillssql, $params);
        foreach ($skillrecords as $record) {
            $skillsmap[$record->userid][$record->name] = (int)$record->points;
        }

        $badgesmap = [];
        $badgeutil = new \local_evokegame\util\badge();
        $badgessql = "SELECT bi.userid, b.name, b.id as badgeid
                        FROM {badge_issued} bi
                        JOIN {badge} b ON b.id = bi.badgeid
                       WHERE b.courseid = :courseid
                         AND bi.userid {$insql}";
        $badgerecords = $DB->get_records_sql($badgessql, $params);
        foreach ($badgerecords as $record) {
            $badgesmap[$record->userid][$record->badgeid] = $record->name;
        }

        $activitiesmap = [];
        $activitysql = "SELECT DISTINCT s.id AS id, s.userid, a.name, cm.id AS cmid
                          FROM {assign_submission} s
                          JOIN {assign} a ON a.id = s.assignment
                          JOIN {modules} m ON m.name = :modname
                          JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = m.id
                         WHERE a.course = :courseid
                           AND s.status IN (:submitted, :reopened)
                           AND s.userid {$insql}";
        $activityparams = $params + ['submitted' => 'submitted', 'reopened' => 'reopened'];
        $activityparams['modname'] = 'assign';
        $activityrecords = $DB->get_records_sql($activitysql, $activityparams);
        foreach ($activityrecords as $record) {
            $activitiesmap[$record->userid][] = [
                'name' => $record->name,
                'url' => new \moodle_url('/mod/assign/view.php', ['id' => $record->cmid])
            ];
        }

        $dbman = $DB->get_manager();
        $entries = new \xmldb_table('portfoliobuilder_entries');
        $portfolio = new \xmldb_table('portfoliobuilder');
        if ($dbman->table_exists($entries) && $dbman->table_exists($portfolio)) {
            $portfolioSql = "SELECT e.id AS id, e.userid, p.name, cm.id AS cmid, e.id AS entryid
                               FROM {portfoliobuilder_entries} e
                               JOIN {portfoliobuilder} p ON p.id = e.portfolioid
                               JOIN {modules} m ON m.name = :modname
                               JOIN {course_modules} cm ON cm.instance = p.id AND cm.module = m.id
                              WHERE e.courseid = :courseid
                                AND e.userid {$insql}
                              ORDER BY e.timecreated ASC";
            $portfolioParams = $params + ['modname' => 'portfoliobuilder'];
            $portfoliorecords = $DB->get_records_sql($portfolioSql, $portfolioParams);
            foreach ($portfoliorecords as $record) {
                $activitiesmap[$record->userid][] = [
                    'name' => get_string('activity_portfolio_entry', 'local_evokegame', [
                        'name' => $record->name,
                        'id' => $record->entryid
                    ]),
                    'url' => new \moodle_url('/mod/portfoliobuilder/view.php', ['id' => $record->cmid])
                ];
            }
        }

        $rows = [];
        foreach ($enrolled as $user) {
            $groups = groups_get_all_groups($courseid, $user->id, 0, 'g.id,g.name');
            $groupnames = [];
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $groupnames[] = $group->name;
                }
            }

            $skillslist = [];
            if (!empty($skillsmap[$user->id])) {
                foreach ($skillsmap[$user->id] as $skillname => $points) {
                    $skillslist[] = [
                        'name' => $skillname,
                        'points' => $points
                    ];
                }
            }
            $badgeslist = [];
            if (!empty($badgesmap[$user->id])) {
                foreach ($badgesmap[$user->id] as $badgeid => $badgename) {
                    $badgeslist[] = [
                        'name' => $badgename,
                        'image' => $badgeutil->get_badge_image_url($context->id, $badgeid)
                    ];
                }
            }
            $activitieslist = $activitiesmap[$user->id] ?? [];

            $rows[] = [
                'name' => fullname($user),
                'email' => $user->email,
                'groups' => !empty($groupnames) ? implode(', ', $groupnames) : '-',
                'skills' => $skillslist,
                'badges' => $badgeslist,
                'activities' => $activitieslist,
                'has_skills' => !empty($skillslist),
                'has_badges' => !empty($badgeslist),
                'has_activities' => !empty($activitieslist),
            ];
        }

        return $rows;
    }
}
