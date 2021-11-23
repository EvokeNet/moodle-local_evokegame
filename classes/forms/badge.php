<?php

namespace local_evokegame\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

use local_evokegame\util\badge as badgeutil;

/**
 * The mform class for creating a badge
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badge extends \moodleform {

    /**
     * Class constructor.
     *
     * @param array $formdata
     * @param array $customodata
     */
    public function __construct($formdata, $customodata = null) {
        parent::__construct(null, $customodata, 'post',  '', ['class' => 'evokegame-badge-form'], true, $formdata);

        $this->set_display_vertical();
    }

    /**
     * The form definition.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        $mform = $this->_form;

        $id = !(empty($this->_customdata['id'])) ? $this->_customdata['id'] : null;
        $courseid = !(empty($this->_customdata['courseid'])) ? $this->_customdata['courseid'] : null;

        if (!empty($courseid)) {
            $mform->addElement('hidden', 'courseid', $courseid);
        }

        $mform->addElement('hidden', 'id', $id);

        $options = [
            1 => get_string('badgetype_badge', 'local_evokegame'),
            2 => get_string('badgetype_award', 'local_evokegame'),
        ];
        $mform->addElement('select', 'type', get_string('badgetype', 'local_evokegame'), $options);
        $mform->setDefault('type', 1);
        $mform->addRule('type', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'local_evokegame'));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('textarea', 'description', get_string('description', 'badges'), 'wrap="virtual" rows="8" cols="70"');
        $mform->setType('description', PARAM_NOTAGS);
        $mform->addRule('description', null, 'required');

        $imageoptions = array('maxbytes' => 262144, 'accepted_types' => array('optimised_image'));
        $mform->addElement('filepicker', 'image', get_string('newimage', 'badges'), null, $imageoptions);
        $mform->addRule('image', null, 'required');
        $mform->addHelpButton('image', 'badgeimage', 'badges');
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $name = isset($data['name']) ? $data['name'] : null;

        if ($this->is_submitted() && (empty($name) || strlen($name) < 3)) {
            $errors['name'] = get_string('validation:namelen', 'local_evokegame');
        }

        return $errors;
    }
}
