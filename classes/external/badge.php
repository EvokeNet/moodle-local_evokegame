<?php

namespace local_evokegame\external;

use context;
use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use local_evokegame\forms\badge as badgeform;

/**
 * Badge external api class.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badge extends external_api {
    /**
     * Create badge parameters
     *
     * @return external_function_parameters
     */
    public static function create_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'course' => new external_value(PARAM_INT, 'The course id'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the badge form, encoded as a json array')
        ]);
    }

    /**
     * Create badge method
     *
     * @param int $contextid
     * @param int $course
     * @param string $jsonformdata
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function create($contextid, $course, $jsonformdata) {
        global $DB, $PAGE, $CFG, $USER, $SITE;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_parameters(),
            ['contextid' => $contextid, 'course' => $course, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new badgeform($data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $now = time();

        // Creates Moodle Badge.
        require_once($CFG->libdir . '/badgeslib.php');

        $mdlbadge = new \stdClass();
        $mdlbadge->name = $validateddata->name;
        $mdlbadge->description = $validateddata->description;
        $mdlbadge->courseid = $course;
        $mdlbadge->usercreated = $USER->id;
        $mdlbadge->usermodified = $USER->id;
        $mdlbadge->version = '';
        $mdlbadge->type = 2;
        $mdlbadge->imageauthorname = '';
        $mdlbadge->imageauthoremail = '';
        $mdlbadge->imageauthorurl = '';
        $mdlbadge->imagecaption = '';
        $mdlbadge->timecreated = $now;
        $mdlbadge->timemodified = $now;
        $mdlbadge->issuerurl = $CFG->wwwroot;
        $mdlbadge->issuername = $SITE->fullname;
        $mdlbadge->issuercontact = $CFG->badges_defaultissuercontact;

        $mdlbadge->messagesubject = get_string('messagesubject', 'badges');
        $mdlbadge->message = get_string('messagebody', 'badges',
            \html_writer::link($CFG->wwwroot . '/badges/mybadges.php', get_string('managebadges', 'badges')));
        $mdlbadge->attachment = 1;
        $mdlbadge->notification = BADGE_MESSAGE_NEVER;
        $mdlbadge->status = BADGE_STATUS_ACTIVE;

        $mdlbadgeid = $DB->insert_record('badge', $mdlbadge);

        // Add badges criterias.
        $badgecriteria = new \stdClass();

        $badgecriteria->badgeid = $mdlbadgeid;
        $badgecriteria->criteriatype = 0;
        $badgecriteria->method = 1;
        $badgecriteria->description = '';
        $badgecriteria->descriptionformat = 1;

        $DB->insert_record('badge_criteria', $badgecriteria);

        $badgecriteria->criteriatype = 2;
        $badgecriteria->method = 2;

        $DB->insert_record('badge_criteria', $badgecriteria);

        $eventparams = array('objectid' => $mdlbadgeid, 'context' => $PAGE->context);
        $event = \core\event\badge_created::create($eventparams);
        $event->trigger();

        $newbadge = new \core_badges\badge($mdlbadgeid);

        badges_process_badge_image($newbadge, $mform->save_temp_file('image'));

        $evokebadge = new \stdClass();
        $evokebadge->courseid = $course;
        $evokebadge->badgeid = $mdlbadgeid;
        $evokebadge->type = $validateddata->type;
        $evokebadge->name = $validateddata->name;
        $evokebadge->timecreated = time();
        $evokebadge->timemodified = time();

        $evokebadgeid = $DB->insert_record('evokegame_badges', $evokebadge);

        $evokebadge->id = $evokebadgeid;

        return [
            'status' => 'ok',
            'message' => get_string('createbadge_success', 'local_evokegame'),
            'data' => json_encode($evokebadge)
        ];
    }

    /**
     * Create badge return fields
     *
     * @return external_single_structure
     */
    public static function create_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }

    /**
     * Create badge parameters
     *
     * @return external_function_parameters
     */
    public static function edit_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the badge form, encoded as a json array')
        ]);
    }

    /**
     * Create badge method
     *
     * @param int $contextid
     * @param string $jsonformdata
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function edit($contextid, $jsonformdata) {
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::edit_parameters(),
            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new badgeform($data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $badge = new \stdClass();
        $badge->id = $validateddata->id;
        $badge->name = $validateddata->name;
        $badge->badgeid = $validateddata->badgeid;
        $badge->type = $validateddata->type;
        $badge->timemodified = time();

        $DB->update_record('evokegame_badges', $badge);

        return [
            'status' => 'ok',
            'message' => get_string('editbadge_success', 'local_evokegame'),
            'data' => json_encode($badge)
        ];
    }

    /**
     * Create badge return fields
     *
     * @return external_single_structure
     */
    public static function edit_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }

    /**
     * Delete badge parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'badge' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The badge id', VALUE_REQUIRED)
            ])
        ]);
    }

    /**
     * Delete badge method
     *
     * @param array $badge
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function delete($badge) {
        global $DB, $PAGE;

        self::validate_parameters(self::delete_parameters(), ['badge' => $badge]);

        $badge = (object)$badge;

        $badgedb = $DB->get_record('evokegame_badges', ['id' => $badge->id], '*', MUST_EXIST);

        $context = \context_course::instance($badgedb->courseid);
        $PAGE->set_context($context);

        $newbadge = new \core_badges\badge($badgedb->badgeid);
        $newbadge->delete(false);

        $DB->delete_records('evokegame_badges', ['id' => $badgedb->id]);

        return [
            'status' => 'ok',
            'message' => get_string('deletebadge_success', 'local_evokegame')
        ];
    }

    /**
     * Delete badge return fields
     *
     * @return external_single_structure
     */
    public static function delete_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_TEXT, 'Return message')
            )
        );
    }
}