<?php

namespace local_evokegame\forms;

use local_evokegame\util\skill;
use local_evokegame\util\badgecriteria as badgecriteriautil;

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
            0 => get_string('chooseanoption', 'local_evokegame'),
            badgecriteriautil::CRITERIA_SKILL_POINTS => get_string('subplugintype_evokebadgecriteria_skillpoints', 'local_evokegame'),
            badgecriteriautil::CRITERIA_COURSE_ACCESS => get_string('subplugintype_evokebadgecriteria_courseaccess', 'local_evokegame'),
            badgecriteriautil::CRITERIA_SKILL_POINTS_AGGREGATION => get_string('subplugintype_evokebadgecriteria_skillpointsaggregation', 'local_evokegame')
        ];

        $mform->addElement('select', 'method', get_string('criteriamethod', 'local_evokegame'), $criteriamethods);
        $mform->setType('method', PARAM_INT);
        $mform->addRule('method', null, 'required', null, 'client');

        $skillutil = new skill();
        $options = $skillutil->get_course_skills_select($courseid);
        $mform->addElement('select', 'skilltarget', get_string('criteriavalue', 'local_evokegame'), $options);
        $mform->setType('skilltarget', PARAM_INT);
        $mform->hideIf('skilltarget', 'method', 'neq', '1');

        $options[0] = null;
        $mform->addElement('select', 'skilltargetaggregation', get_string('criteriavalue', 'local_evokegame'), $options);
        $mform->setType('skilltargetaggregation', PARAM_INT);
        $mform->hideIf('skilltargetaggregation', 'method', 'neq', '3');
        $mform->getElement('skilltargetaggregation')->setMultiple(true);

        $mform->addElement('text', 'value', get_string('value', 'local_evokegame'));
        $mform->addRule('value', get_string('required'), 'required', null, 'client');
        $mform->setType('value', PARAM_INT);
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

        if ($this->is_submitted() && !empty($method) && $method == 1 && empty($skilltarget)) {
            $errors['skilltarget'] = get_string('required');
        }

        if ($this->is_submitted() && !empty($method) && $method == 3) {
            if (count($skilltargetaggregation) == 1 && $skilltargetaggregation[0] == 0) {
                $errors['skilltargetaggregation'] = get_string('required');
            }
        }

        if ($this->is_submitted() && (empty($value))) {
            $errors['value'] = get_string('required');
        }

        return $errors;
    }
}
