<?php

/**
 * Prints local_evokegame scoreboard.
 *
 * @package     mod_evokeportfolio
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

$urlparams = ['id' => $course->id];

$context = context_course::instance($course->id);

$user = $USER;
if (!empty($userid)) {
    $urlparams['userid'] = $userid;

    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
}

require_course_login($course, true);

$PAGE->set_url('/local/evokegame/profile.php', $urlparams);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_evokegame');

$contentrenderable = new \local_evokegame\output\profile($context, $course, $user);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
