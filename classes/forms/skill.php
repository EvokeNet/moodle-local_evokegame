<?php

namespace local_evokegame\forms;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');

class skill extends \moodleform {
    /**
     * Class constructor.
     *
     * @param array $formdata
     * @param array $customodata
     */
    public function __construct($formdata, $customodata = null) {
        parent::__construct(null, $customodata, 'post',  '', ['class' => 'evokegame-skills-form'], true, $formdata);

        $this->set_display_vertical();
    }

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_evokegame'), ['style' => 'width: 100%;']);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $this->add_action_buttons(true);
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

        $skillutil = new \local_evokegame\util\skill();

        if ($this->is_submitted() && empty($name)) {
            $errors['name'] = get_string('required');
        }

        if ($this->is_submitted() && !empty($name) && $skillutil->skill_exists($data['courseid'], $name)) {
            $errors['name'] = get_string('skills_nameinuse', 'local_evokegame');
        }

        return $errors;
    }
}
