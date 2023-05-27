<?php

namespace local_evokegame\external;

use context;
use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use local_evokegame\forms\skill as skillform;

/**
 * Skill external api class.
 *
 * @package     local_evokegame
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class skill extends external_api {
    /**
     * Create skill parameters
     *
     * @return external_function_parameters
     */
    public static function create_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'courseid' => new external_value(PARAM_INT, 'The course id'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the skill form, encoded as a json array')
        ]);
    }

    /**
     * Create skill method
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
    public static function create($contextid, $courseid, $jsonformdata) {
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_parameters(),
            ['contextid' => $contextid, 'courseid' => $courseid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new skillform($data, $data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $skillutil = new \local_evokegame\util\skill();

        if ($skillutil->skill_exists($courseid, $validateddata->name)) {
            throw new \Exception('Duplicated entry');
        }

        $skill = $skillutil->create($courseid, $validateddata->name);

        return [
            'status' => 'ok',
            'message' => get_string('skills_create_success', 'local_evokegame'),
            'data' => json_encode($skill)
        ];
    }

    /**
     * Create skill return fields
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
     * Delete skill parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The skill id', VALUE_REQUIRED)
        ]);
    }

    /**
     * Delete skill method
     *
     * @param array $skill
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function delete($id) {
        self::validate_parameters(self::delete_parameters(), ['id' => $id]);

        $skillutil = new \local_evokegame\util\skill();

        $skillutil->delete($id);

        return [
            'status' => 'ok',
            'message' => get_string('skills_delete_success', 'local_evokegame')
        ];
    }

    /**
     * Delete skill return fields
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