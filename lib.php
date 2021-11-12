<?php

/**
 * Plugin lib.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Inject the custom fields elements into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function local_evokegame_coursemodule_standard_elements($formwrapper, $mform) {
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
        $url = new moodle_url('/local/evokegame/superpower.php', array('id' => $course->id));

        $navigation->add(
            get_string('superpowerssettings', 'local_evokegame'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'superpowerssettings',
            new pix_icon('t/award', '')
        );
    }
}

function local_evokegame_output_fragment_superpower_form($args) {
    $args = (object) $args;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $mform = new \local_evokegame\forms\superpower($formdata, [
        'id' => $serialiseddata->id,
        'courseid' => $serialiseddata->courseid,
        'badgeid' => $serialiseddata->badgeid,
        'name' => $serialiseddata->name
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