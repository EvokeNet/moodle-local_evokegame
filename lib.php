<?php

/**
 * Plugin lib.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

use local_evokegame\util\game;

defined('MOODLE_INTERNAL') || die();

/**
 * Inject the custom fields elements into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function local_evokegame_coursemodule_standard_elements($formwrapper, $mform) {
    $course = $formwrapper->get_course();

    if (!game::is_enabled_in_course($course->id)) {
        return;
    }

    // Evocoins.
    $options = [0 => get_string('chooseavalue', 'local_evokegame')];
    foreach (range(1, 100) as $option) {
        $options[$option] = $option;
    }
    $mform->addElement('header', 'evocoinheader', get_string('evocoins', 'local_evokegame'));
    $mform->addElement('select', 'evocoins', get_string('evocoins', 'local_evokegame'), $options);
    $mform->setType('evocoins', PARAM_INT);
    $mform->disabledIf('evocoins', 'completion', 'eq', 0);

    $evocoinutil = new \local_evokegame\util\evocoinmodule();
    if ($formwrapper->get_coursemodule() && $evocoins = $evocoinutil->get_module_coins($formwrapper->get_coursemodule()->id)) {
        $mform->setDefault('evocoins', $evocoins);
    }

    // Skill points.
    if (!in_array($formwrapper->get_current()->modulename, ['evokeportfolio', 'portfoliobuilder', 'portfoliogroup'])) {
        return;
    }

    $skillutil = new \local_evokegame\util\skill();
    $skills = $skillutil->get_course_skills($course->id);

    $dbskillssubmission = false;
    $dbskillscomment = false;
    $dbskillslike = false;
    $dbskillsgrade = false;
    if ($formwrapper->get_coursemodule()) {
        $cmid = $formwrapper->get_coursemodule()->id;

        $skillmodule = new \local_evokegame\util\skillmodule();

        $dbskillssubmission = $skillmodule->get_module_skills($cmid, 'submission');
        $dbskillscomment = $skillmodule->get_module_skills($cmid, 'comment');
        $dbskillslike = $skillmodule->get_module_skills($cmid, 'like');
        $dbskillsgrade = $skillmodule->get_module_skills($cmid, 'grading');
    }

    // Skills submission.
    $mform->addElement('header', 'skills_submission_header', get_string('skills_submission', 'local_evokegame'));

    $url = new moodle_url('/local/evokegame/skillsettings.php', ['id' => $formwrapper->get_current()->course]);
    $alert = '<div class="alert alert-info mx-4">';
    $alert .= '<p>'. get_string('skills_submission_desc', 'local_evokegame') .'</p>';
    $alert .= '<p class="mb-0"><a href="'.$url.'">'. get_string('skills_manage', 'local_evokegame') .'</a></p>';
    $alert .= '</div>';

    $mform->addElement('html', $alert);

    foreach ($skills as $skill) {
        $fieldname = "evokegameskillssubmission[$skill->id]";

        $options = [0 => get_string('chooseavalue', 'local_evokegame')];
        foreach (range(1, 100) as $option) {
            $options[$option] = $option;
        }

        $mform->addElement('select', $fieldname, $skill->name, $options);
        $mform->setType($fieldname, PARAM_INT);

        if ($dbskillssubmission && isset($dbskillssubmission[$skill->id])) {
            $mform->setDefault($fieldname, $dbskillssubmission[$skill->id]->value);
        }
    }

    // Skills comment.
    $mform->addElement('header', 'skills_comment_header', get_string('skills_comment', 'local_evokegame'));

    $url = new moodle_url('/local/evokegame/skillsettings.php', ['id' => $formwrapper->get_current()->course]);
    $alert = '<div class="alert alert-info mx-4">';
    $alert .= '<p>'. get_string('skills_comment_desc', 'local_evokegame') .'</p>';
    $alert .= '<p class="mb-0"><a href="'.$url.'">'. get_string('skills_manage', 'local_evokegame') .'</a></p>';
    $alert .= '</div>';

    $mform->addElement('html', $alert);

    foreach ($skills as $skill) {
        $fieldname = "evokegameskillscomment[$skill->id]";

        $options = [0 => get_string('chooseavalue', 'local_evokegame')];
        foreach (range(1, 100) as $option) {
            $options[$option] = $option;
        }

        $mform->addElement('select', $fieldname, $skill->name, $options);
        $mform->setType($fieldname, PARAM_INT);

        if ($dbskillscomment && isset($dbskillscomment[$skill->id])) {
            $mform->setDefault($fieldname, $dbskillscomment[$skill->id]->value);
        }
    }

    // Skills like.
    $mform->addElement('header', 'skills_like_header', get_string('skills_like', 'local_evokegame'));

    $url = new moodle_url('/local/evokegame/skillsettings.php', ['id' => $formwrapper->get_current()->course]);
    $alert = '<div class="alert alert-info mx-4">';
    $alert .= '<p>'. get_string('skills_like_desc', 'local_evokegame') .'</p>';
    $alert .= '<p class="mb-0"><a href="'.$url.'">'. get_string('skills_manage', 'local_evokegame') .'</a></p>';
    $alert .= '</div>';

    $mform->addElement('html', $alert);

    foreach ($skills as $skill) {
        $fieldname = "evokegameskillslike[$skill->id]";

        $options = [0 => get_string('chooseavalue', 'local_evokegame')];
        foreach (range(1, 100) as $option) {
            $options[$option] = $option;
        }

        $mform->addElement('select', $fieldname, $skill->name, $options);
        $mform->setType($fieldname, PARAM_INT);

        if ($dbskillslike && isset($dbskillslike[$skill->id])) {
            $mform->setDefault($fieldname, $dbskillslike[$skill->id]->value);
        }
    }

    // Skills grade.
    $mform->addElement('header', 'skills_grade_header', get_string('skills_grade', 'local_evokegame'));

    $url = new moodle_url('/local/evokegame/skillsettings.php', ['id' => $formwrapper->get_current()->course]);
    $alert = '<div class="alert alert-info mx-4">';
    $alert .= '<p>'. get_string('skills_grade_desc', 'local_evokegame') .'</p>';
    $alert .= '<p class="mb-0"><a href="'.$url.'">'. get_string('skills_manage', 'local_evokegame') .'</a></p>';
    $alert .= '</div>';

    $mform->addElement('html', $alert);

    foreach ($skills as $skill) {
        $fieldname = "evokegameskillsgrade[$skill->id]";

        $options = [0 => get_string('chooseavalue', 'local_evokegame')];
        foreach (range(1, 100) as $option) {
            $options[$option] = $option;
        }

        $mform->addElement('select', $fieldname, $skill->name, $options);
        $mform->setType($fieldname, PARAM_INT);

        if ($dbskillsgrade && isset($dbskillsgrade[$skill->id])) {
            $mform->setDefault($fieldname, $dbskillsgrade[$skill->id]->value);
        }
    }
}

/**
 * Saves the data of custom fields elements of all moodle module settings forms.
 *
 * @param object $moduleinfo the module info
 * @param object $course the course of the module
 */
function local_evokegame_coursemodule_edit_post_actions($moduleinfo, $course) {
    $evocoins = $moduleinfo->evocoins ?? null;
    $skillssubmission = $moduleinfo->evokegameskillssubmission ?? null;
    $skillscomments = $moduleinfo->evokegameskillscomment ?? null;
    $skillslikes = $moduleinfo->evokegameskillslike ?? null;
    $skillsgrade = $moduleinfo->evokegameskillsgrade ?? null;

    $evocoinutil = new \local_evokegame\util\evocoinmodule();

    $evocoinutil->sync_module_coins($moduleinfo->coursemodule, $evocoins);

    $educoinutil = new \local_evokegame\util\skillmodule();

    $educoinutil->sync_module_skills($moduleinfo->coursemodule, 'submission', $skillssubmission);
    $educoinutil->sync_module_skills($moduleinfo->coursemodule, 'comment', $skillscomments);
    $educoinutil->sync_module_skills($moduleinfo->coursemodule, 'like', $skillslikes);
    $educoinutil->sync_module_skills($moduleinfo->coursemodule, 'grading', $skillsgrade);

    return $moduleinfo;
}

function local_evokegame_output_fragment_chooseavatar_form($args) {
    GLOBAL $CFG;

    $args = (object) $args;

    $o = html_writer::start_div('chooseavatar-form');

    for ($i = 1; $i < 33; $i++) {
        $url = $CFG->wwwroot . '/local/evokegame/pix/a' . $i . '.svg';
        $o .= html_writer::img($url, 'avatar', ['class' => 'avatar', 'data-id' => $i]);
    }

    $o .= html_writer::end_div();

    return $o;
}

function local_evokegame_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:update', $context)) {
        $url = new moodle_url('/local/evokegame/coursesettings.php', ['id' => $course->id]);
        $navigation->add(
            get_string('coursesettings', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'evokegamecoursesettings',
            new pix_icon('i/course', '')
        );

        $url = new moodle_url('/local/evokegame/skillsettings.php', ['id' => $course->id]);
        $navigation->add(
            get_string('skills_settings', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'evokegameskillsettings',
            new pix_icon('i/course', '')
        );

        $url = new moodle_url('/local/evokegame/badge.php', ['id' => $course->id]);
        $navigation->add(
            get_string('badgessettings', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'evokegamebadgessettings',
            new pix_icon('t/award', '')
        );

        $url = new moodle_url('/local/evokegame/report.php', ['id' => $course->id]);
        $navigation->add(
            get_string('game_report', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'evokegamereport',
            new pix_icon('i/course', '')
        );
    }
}

function local_evokegame_output_fragment_badge_form($args) {
    $args = (object) $args;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        $formdata = (array) $serialiseddata;
    }

    $mform = new \local_evokegame\forms\badge($formdata, [
        'id' => $serialiseddata->id,
        'name' => $serialiseddata->name,
        'description' => $serialiseddata->description,
        'type' => $serialiseddata->type,
        'highlight' => $serialiseddata->highlight,
        'courseid' => $serialiseddata->courseid,
        'badgeid' => $serialiseddata->badgeid,
    ]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

function local_evokegame_output_fragment_badgecriteria_form($args) {
    $args = (object) $args;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        $formdata = (array)$serialiseddata;
    }

    $mform = new \local_evokegame\forms\badgecriteria($formdata, [
        'courseid' => $serialiseddata->courseid,
        'badgeid' => $serialiseddata->badgeid,
    ]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

/**
 * Returns create skill form fragment.
 *
 * @param $args
 * @return string
 */
function local_evokegame_output_fragment_skill_form($args) {
    $args = (object) $args;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        $formdata = (array)$serialiseddata;
    }

    $mform = new \local_evokegame\forms\skill($formdata, [
        'courseid' => $serialiseddata->courseid,
    ]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

/**
 * Add callback to invoke conversion of bootstrap alert to Toastr notifications
 *
 * @return void
 */
function local_evokegame_before_footer() {
    global $PAGE;

    $PAGE->requires->js_call_amd('local_evokegame/alerttotoastr', 'init');
}

/**
 * Serves the files from the local_evokegame file areas.
 *
 * @package     local_evokegame
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The local_evokegame's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function local_evokegame_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    if ($context->contextlevel != CONTEXT_COURSE) {
        send_file_not_found();
    }

    require_login($course, false, $cm);

    $itemid = (int)array_shift($args);
    if ($itemid == 0) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/local_evokegame/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

function local_evokegame_moove_additional_header() {
    global $PAGE;

    if (isguestuser() || !isloggedin()) {
        return false;
    }

    $context = \context_course::instance($PAGE->course->id);

    if (!is_enrolled($context)) {
        return false;
    }

    $evokegame = new \local_evokegame\output\evokegame();

    return $evokegame->get_dashboardnavbar($PAGE->course, $context);
}
