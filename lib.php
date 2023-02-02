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

    // Add custom fields to the form.
    $handler = local_evokegame\customfield\mod_handler::create();
    $handler->set_parent_context($formwrapper->get_context()); // For course handler only.

    $cm = $formwrapper->get_coursemodule();

    if (empty($cm)) {
        $cmid = 0;
    } else {
        $cmid = $cm->id;
    }

    $handler->instance_form_definition($mform, $cmid);

    // Prepare custom fields data.
    $data = $formwrapper->get_current();

    $oldid = $data->id;

    $data->id = $cmid;

    $handler->instance_form_before_set_data($data);

    $data->id = $oldid;
}

/**
 * Validates the custom fields elements of all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param \stdClass $data The form data.
 */
function local_evokegame_coursemodule_validation($formwrapper, $data) {
    // Add the custom fields validation.
    $handler = local_evokegame\customfield\mod_handler::create();

    return $handler->instance_form_validation($data, []);
}

/**
 * Saves the data of custom fields elements of all moodle module settings forms.
 *
 * @param object $moduleinfo the module info
 * @param object $course the course of the module
 */
function local_evokegame_coursemodule_edit_post_actions($moduleinfo, $course) {
    // Save custom fields if there are any of them in the form.
    $handler = local_evokegame\customfield\mod_handler::create();

    // Make sure to set the handler's parent context first.
    $context = context_module::instance($moduleinfo->coursemodule);
    $handler->set_parent_context($context);

    // Save the custom field data.
    $moduleinfo->id = $moduleinfo->coursemodule;
    $handler->instance_form_save($moduleinfo, true);

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
        $url = new moodle_url('/local/evokegame/badge.php', array('id' => $course->id));

        $navigation->add(
            get_string('badgessettings', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'badgessettings',
            new pix_icon('t/award', '')
        );

        $url = new moodle_url('/local/evokegame/coursesettings.php', array('id' => $course->id));

        $navigation->add(
            get_string('coursesettings', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'evokegamecoursesettings',
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
 * Add callback to invoke conversion of bootstrap alert to Toastr notifications
 *
 * @return void
 */
function local_evokegame_before_footer() {
    global $PAGE;

    //$PAGE->requires->js_call_amd('local_evokegame/alerttotoastr', 'init');
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
