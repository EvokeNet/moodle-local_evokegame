<?php

namespace local_evokegame\forms;

use local_evokegame\util\skill;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating a badge
 *
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \moodleform {

    /**
     * Class constructor.
     *
     * @param array $formdata
     * @param array $customodata
     */
    public function __construct($formdata, $customodata = null) {
        parent::__construct(null, $customodata, 'post',  '', ['class' => 'evokegame-badgecriterias-form'], true, $formdata);

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

        $courseid = !(empty($this->_customdata['courseid'])) ? $this->_customdata['courseid'] : null;
        $badgeid = !(empty($this->_customdata['badgeid'])) ? $this->_customdata['badgeid'] : null;

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->addElement('hidden', 'badgeid', $badgeid);

        $criteriamethods = [
            null => get_string('chooseanoption', 'local_evokegame')
        ];

        $installedcriterias = \core_plugin_manager::instance()->get_plugins_of_type('evokegamebadgecriteria');
        if ($installedcriterias) {
            foreach ($installedcriterias as $criteria) {
                $criteriamethods[$criteria->name] = $criteria->displayname;
            }
        }

        $mform->addElement('select', 'method', get_string('criteriamethod', 'local_evokegame'), $criteriamethods);
        $mform->setType('method', PARAM_ALPHANUM);
        $mform->addRule('method', null, 'required', null, 'client');

        $skillutil = new skill();
        $options = $skillutil->get_course_skills_select($courseid);
        $mform->addElement('select', 'skilltarget', get_string('criteriavalue', 'local_evokegame'), $options);
        $mform->setType('skilltarget', PARAM_INT);
        $mform->hideIf('skilltarget', 'method', 'neq', 'skillpoints');

        $options[0] = null;
        $mform->addElement('select', 'skilltargetaggregation', get_string('criteriavalue', 'local_evokegame'), $options);
        $mform->setType('skilltargetaggregation', PARAM_INT);
        $mform->hideIf('skilltargetaggregation', 'method', 'neq', 'skillpointsaggregation');
        $mform->getElement('skilltargetaggregation')->setMultiple(true);

        $mform->addElement('text', 'value', get_string('value', 'local_evokegame'));
        $mform->setType('value', PARAM_INT);
        $mform->setDefault('value', '0');
        $mform->hideIf('value', 'method', 'eq', 'coursecompletion');
        $mform->disabledIf('value', 'method', 'eq', 'coursecompletion');
        // Value is required for all methods except coursecompletion - we'll handle this in validation
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

        $method = isset($data['method']) ? $data['method'] : null;
        $value = isset($data['value']) ? $data['value'] : null;
        $skilltarget = isset($data['skilltarget']) ? $data['skilltarget'] : null;
        $skilltargetaggregation = isset($data['skilltargetaggregation']) ? $data['skilltargetaggregation'] : null;

        if ($this->is_submitted() && empty($method)) {
            $errors['method'] = get_string('required');
        }

        if ($this->is_submitted() && empty($method)) {
            $errors['method'] = get_string('required');
        }

        if ($this->is_submitted() && !empty($method) && $method == 'skillpoints' && empty($skilltarget)) {
            $errors['skilltarget'] = get_string('required');
        }

        if ($this->is_submitted() && !empty($method) && $method == 'skillpointsaggregation') {
            if (count($skilltargetaggregation) == 1 && $skilltargetaggregation[0] == 0) {
                $errors['skilltargetaggregation'] = get_string('required');
            }
        }

        // Value is not required for coursecompletion criteria
        if ($this->is_submitted() && (empty($value)) && $method !== 'coursecompletion') {
            $errors['value'] = get_string('required');
        }

        return $errors;
    }
}
