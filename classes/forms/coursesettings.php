<?php

namespace local_evokegame\forms;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');

class coursesettings extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $options = [
            0 => get_string('no'),
            1 => get_string('yes'),
        ];
        $mform->addElement('select', 'isgameenabledincourse', get_string('isgameenabledincourse', 'local_evokegame'), $options);
        $mform->addRule('isgameenabledincourse', null, 'required', null, 'client');
        $this->fill_field_with_database_value('isgameenabledincourse');

        $mform->addElement('text', 'scoreboard_prize', get_string('scoreboard_prize', 'local_evokegame'), ['style' => 'width: 100%;']);
        $mform->setType('scoreboard_prize', PARAM_TEXT);
        $this->fill_field_with_database_value('scoreboard_prize');

        $mform->addElement('date_time_selector', 'scoreboard_finishdate', get_string('scoreboard_finishdate', 'local_evokegame'));
        $mform->addHelpButton('scoreboard_finishdate', 'scoreboard_finishdate', 'local_evokegame');
        $this->fill_field_with_database_value('scoreboard_finishdate');

        $mform->addElement('filepicker', 'scoreboard_image', get_string('scoreboard_image', 'local_evokegame'), null,
            ['maxbytes' => 1, 'accepted_types' => 'optimised_image']);

        $mform->addElement('textarea', 'coursemenuitems', get_string('coursemenuitems', 'local_evokegame'), 'wrap="virtual" rows="10" cols="100"');
        $mform->setType('coursemenuitems', PARAM_RAW);
        $mform->addHelpButton('coursemenuitems','coursemenuitems',  'local_evokegame');
        $this->fill_field_with_database_value('coursemenuitems');

        $mform->addElement('html', '<div class="row"><div class="col-md-3"></div><div class="col-md-9">'.get_string('coursemenuitems_help', 'local_evokegame').'</div></div>');

        $this->add_action_buttons(true);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    /**
     * Dummy stub method - override if you need to setup the form depending on current
     * values. This method is called after definition(), data submission and set_data().
     * All form setup that is dependent on form values should go in here.
     */
    function definition_after_data() {
        $mform = $this->_form;

        $context = \context_course::instance($this->_customdata['courseid']);
        $draftitemid = file_get_submitted_draft_itemid('scoreboard_image');

        file_prepare_draft_area($draftitemid, $context->id, 'local_evokegame', 'scoreboard_image', $this->_customdata['courseid'], ['maxbytes' => 1, 'accepted_types' => 'optimised_image']);

        $mform->getElement('scoreboard_image')->setValue($draftitemid);
    }

    private function fill_field_with_database_value($fieldname) {
        $fielddbname = $fieldname . '-' . $this->_customdata['courseid'];

        $fieldvalue = get_config('local_evokegame', $fielddbname);

        if ($fieldvalue === false) {
            return false;
        }

        $mform = $this->_form;

        $mform->setDefault($fieldname, $fieldvalue);
    }
}