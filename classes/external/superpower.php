<?php

namespace local_evokegame\external;

use context;
use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use local_evokegame\forms\superpower as superpowerform;

/**
 * Avatar external api class.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class superpower extends external_api {
    /**
     * Create superpower parameters
     *
     * @return external_function_parameters
     */
    public static function create_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'course' => new external_value(PARAM_INT, 'The course id'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the superpower form, encoded as a json array')
        ]);
    }

    /**
     * Create superpower method
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
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_parameters(),
            ['contextid' => $contextid, 'course' => $course, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new superpowerform($data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $superpower = new \stdClass();
        $superpower->courseid = $course;
        $superpower->name = $validateddata->name;
        $superpower->badgeid = $validateddata->badgeid;
        $superpower->timecreated = time();
        $superpower->timemodified = time();

        $superpowerid = $DB->insert_record('evokegame_superpowers', $superpower);

        $superpower->id = $superpowerid;

        return [
            'status' => 'ok',
            'message' => get_string('createsuperpower_success', 'local_evokegame'),
            'data' => json_encode($superpower)
        ];
    }

    /**
     * Create superpower return fields
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
     * Create superpower parameters
     *
     * @return external_function_parameters
     */
    public static function edit_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the superpower form, encoded as a json array')
        ]);
    }

    /**
     * Create superpower method
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

        $mform = new superpowerform($data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $superpower = new \stdClass();
        $superpower->id = $validateddata->id;
        $superpower->name = $validateddata->name;
        $superpower->badgeid = $validateddata->badgeid;
        $superpower->timemodified = time();

        $DB->update_record('evokegame_superpowers', $superpower);

        return [
            'status' => 'ok',
            'message' => get_string('editsuperpower_success', 'local_evokegame'),
            'data' => json_encode($superpower)
        ];
    }

    /**
     * Create superpower return fields
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
     * Delete superpower parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'superpower' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The superpower id', VALUE_REQUIRED)
            ])
        ]);
    }

    /**
     * Delete superpower method
     *
     * @param array $superpower
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function delete($superpower) {
        global $DB;

        self::validate_parameters(self::delete_parameters(), ['superpower' => $superpower]);

        $superpower = (object)$superpower;

        $DB->delete_records('evokegame_superpowers', ['id' => $superpower->id]);

        return [
            'status' => 'ok',
            'message' => get_string('deletesuperpower_success', 'local_evokegame')
        ];
    }

    /**
     * Delete superpower return fields
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