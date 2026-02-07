<?php

namespace local_evokegame\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class badge_delivery extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $courseid = $customdata['courseid'] ?? 0;
        $badgeid = $customdata['badgeid'] ?? 0;

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'badgeid', $badgeid);
        $mform->setType('badgeid', PARAM_INT);

        $options = [];
        if (!empty($courseid)) {
            $context = \context_course::instance($courseid);
            $users = get_enrolled_users($context, 'moodle/course:viewparticipants', 0, 'u.id,u.firstname,u.lastname,u.email');
            foreach ($users as $user) {
                $options[$user->id] = fullname($user) . ' (' . $user->email . ')';
            }
        }

        $select = $mform->addElement(
            'select',
            'userids',
            get_string('deliverbadgeusers_select', 'local_evokegame'),
            $options
        );
        $select->setMultiple(true);
        $select->setSize(10);
        $mform->setType('userids', PARAM_INT);
        $mform->addRule('userids', get_string('required'), 'required', null, 'client');
    }
}
