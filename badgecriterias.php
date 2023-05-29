<?php

/**
 * Configure course badgecriterias.
 *
 * @package     mod_evokeportfolio
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

// Course module id.
$id = required_param('id', PARAM_INT);

$evokebadge = $DB->get_record('evokegame_badges', ['id' => $id], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $evokebadge->courseid], '*', MUST_EXIST);

require_course_login($course, true);

$context = context_course::instance($course->id);

if (!has_capability('moodle/course:update', $context)) {
    redirect(new moodle_url('/course/view.php', ['id' => $id]), \core\notification::error('Illegal access!'));
}

$PAGE->set_url('/local/evokegame/badgecriterias.php', ['id' => $course->id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_evokegame');

$contentrenderable = new \local_evokegame\output\badgecriterias($course, $context, $evokebadge);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
