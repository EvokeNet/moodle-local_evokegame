<?php

namespace local_evokegame\forms;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');

class coursesettings extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('textarea', 'coursemenuitems', get_string('coursemenuitems', 'local_evokegame'), 'wrap="virtual" rows="10" cols="100"');
        $mform->setType('coursemenuitems', PARAM_RAW);
        $mform->addHelpButton('coursemenuitems','coursemenuitems',  'local_evokegame');

        $coursemenuitemsconfig = get_config('local_evokegame', 'coursemenuitems-' . $this->_customdata['courseid']);
        if ($coursemenuitemsconfig) {
            $mform->setDefault('coursemenuitems', $coursemenuitemsconfig);
        }

        $mform->addElement('html', '<div class="row"><div class="col-md-3"></div><div class="col-md-9">'.get_string('coursemenuitems_help', 'local_evokegame').'</div></div>');

        $this->add_action_buttons(true);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}