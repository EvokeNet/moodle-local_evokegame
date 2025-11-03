<?php

/**
 * This file contains the evokegame element coursecompletion's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2025 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_coursecompletion;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * The evokegame element coursecompletion's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2025 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_evokegame\badgecriteria {

    /**
     * Check if user has achieved the criteria (all activities submitted).
     *
     * @return bool
     */
    public function user_achieved_criteria(): bool {
        $courseid = $this->badgecriteria->courseid;
        $completiondata = $this->check_course_completion($courseid);

        // Criteria is achieved when all activities are completed
        return $completiondata['completed'] === $completiondata['total'] && $completiondata['total'] > 0;
    }

    /**
     * Get user's progress percentage for this criteria.
     *
     * @return int Progress percentage (0-100)
     */
    public function get_user_criteria_progress(): int {
        $courseid = $this->badgecriteria->courseid;
        $completiondata = $this->check_course_completion($courseid);

        if ($completiondata['total'] == 0) {
            return 0;
        }

        if ($completiondata['completed'] >= $completiondata['total']) {
            return 100;
        }

        return (int)($completiondata['completed'] * 100 / $completiondata['total']);
    }

    /**
     * Get HTML representation of user's progress.
     *
     * @return string HTML progress bar
     */
    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'evokegamebadgecriteria_coursecompletion');
        $progress = $this->get_user_criteria_progress();

        $courseid = $this->badgecriteria->courseid;
        $completiondata = $this->check_course_completion($courseid);

        $langdata = new \stdClass();
        $langdata->completed = $completiondata['completed'];
        $langdata->total = $completiondata['total'];

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'evokegamebadgecriteria_coursecompletion');
        $progressdesc = get_string('criteriaprogresdesc_progress', 'evokegamebadgecriteria_coursecompletion', $langdata);

        return '<p class="mb-0">'.$pluginname.'
                        <a class="btn btn-link p-0"
                           role="button"
                           data-container="body"
                           data-toggle="popover"
                           data-placement="right"
                           data-html="true"
                           tabindex="0"
                           data-trigger="focus"
                           data-content="<div class=\'no-overflow\'><p>'.$criteriaprogresdesc.'</p><p>'.$progressdesc.'</p></div>">
                            <i class="icon fa fa-info-circle text-info fa-fw " title="'.$pluginname.'" role="img" aria-label="'.$pluginname.'"></i>
                        </a>
                    </p>
                    <div class="progress ml-0">
                        <div class="progress-bar" role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100">'.$progress.'%</div>
                    </div>';
    }

    /**
     * Check course completion for user, including group activities.
     *
     * @param int $courseid Course ID
     * @return array Array with 'completed' and 'total' keys
     */
    private function check_course_completion(int $courseid): array {
        global $DB;

        $course = get_course($courseid);
        $completioninfo = new \completion_info($course);
        $modinfo = get_fast_modinfo($courseid);

        // Get all activities with completion tracking enabled
        $activities = $completioninfo->get_activities();

        if (empty($activities)) {
            return ['completed' => 0, 'total' => 0];
        }

        $total = count($activities);
        $completed = 0;

        foreach ($activities as $cm) {
            if ($this->is_activity_completed($cm, $completioninfo)) {
                $completed++;
            }
        }

        return ['completed' => $completed, 'total' => $total];
    }

    /**
     * Check if a specific activity is completed for the user.
     * Handles both individual and group activities.
     *
     * @param \cm_info $cm Course module info
     * @param \completion_info $completioninfo Completion info object
     * @return bool True if completed
     */
    private function is_activity_completed(\cm_info $cm, \completion_info $completioninfo): bool {
        global $DB;

        // Get completion data for this activity
        $completiondata = $completioninfo->get_data($cm, false, $this->userid);

        // If completion state is set and indicates completion, return true
        if (!empty($completiondata->completionstate) &&
            ($completiondata->completionstate == COMPLETION_COMPLETE ||
             $completiondata->completionstate == COMPLETION_COMPLETE_PASS)) {
            return true;
        }

        // For assignments, also check if submission exists (handles group submissions)
        if ($cm->modname === 'assign') {
            return $this->check_assign_submission($cm->instance, $cm->course);
        }

        return false;
    }

    /**
     * Check if assignment has been submitted (handles group submissions).
     *
     * @param int $assignmentid Assignment instance ID
     * @param int $courseid Course ID
     * @return bool True if submitted (individual or as group member)
     */
    private function check_assign_submission(int $assignmentid, int $courseid): bool {
        global $DB;

        // Check for individual submission
        $individualsubmission = $DB->get_record('assign_submission', [
            'assignment' => $assignmentid,
            'userid' => $this->userid,
            'status' => 'submitted'
        ]);

        if ($individualsubmission) {
            return true;
        }

        // Check for group submission - user might be part of a group that submitted
        // Get user's groups in this course
        $usergroups = groups_get_user_groups($courseid, $this->userid);
        if (empty($usergroups[0])) {
            return false;
        }

        // Check if any of user's groups has a submission
        list($insql, $inparams) = $DB->get_in_or_equal($usergroups[0], SQL_PARAMS_NAMED);
        $params = array_merge(['assignment' => $assignmentid, 'status' => 'submitted'], $inparams);

        $groupsubmission = $DB->get_record_sql(
            "SELECT id FROM {assign_submission}
             WHERE assignment = :assignment
             AND status = :status
             AND groupid $insql
             LIMIT 1",
            $params
        );

        return (bool)$groupsubmission;
    }
}

