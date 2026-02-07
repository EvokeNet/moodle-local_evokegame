<?php

namespace local_evokegame\external;

use core\context;
use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;
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
     * Deliver skill points parameters
     *
     * @return external_function_parameters
     */
    public static function deliver_points_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the delivery form, encoded as a json array')
        ]);
    }

    /**
     * Deliver skill points to selected users method
     *
     * @param int $contextid
     * @param string $jsonformdata
     * @return array
     */
    public static function deliver_points($contextid, $jsonformdata) {
        global $DB;

        $params = self::validate_parameters(self::deliver_points_parameters(), [
            'contextid' => $contextid,
            'jsonformdata' => $jsonformdata
        ]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);
        self::validate_context($context);
        require_capability('moodle/course:update', $context);

        $serialiseddata = json_decode($params['jsonformdata']);
        $data = [];
        parse_str($serialiseddata, $data);

        $courseid = !empty($data['courseid']) ? (int)$data['courseid'] : 0;
        $skillmoduleid = !empty($data['skillmoduleid']) ? (int)$data['skillmoduleid'] : 0;
        $userids = $data['userids'] ?? [];
        if (!is_array($userids)) {
            $userids = [$userids];
        }

        if (!$courseid || !$skillmoduleid || empty($userids)) {
            throw new \moodle_exception('invalidparameters');
        }

        $sql = "SELECT sm.id, sm.value, s.courseid
                  FROM {evokegame_skills_modules} sm
                  JOIN {evokegame_skills} s ON s.id = sm.skillid
                 WHERE sm.id = :skillmoduleid";
        $skillmodule = $DB->get_record_sql($sql, ['skillmoduleid' => $skillmoduleid], MUST_EXIST);

        if ((int)$skillmodule->courseid !== (int)$courseid) {
            throw new \moodle_exception('invalidparameters');
        }

        $coursecontext = \context_course::instance($courseid);

        $delivered = 0;
        foreach ($userids as $userid) {
            $userid = (int)$userid;
            if (!$userid || !is_enrolled($coursecontext, $userid)) {
                continue;
            }

            $points = new \local_evokegame\util\point($courseid, $userid);
            $skillpointobject = (object)[
                'skillmoduleid' => $skillmoduleid,
                'value' => (int)$skillmodule->value
            ];
            $points->add_points($skillpointobject);
            $delivered++;
        }

        return [
            'status' => 'ok',
            'message' => get_string('skills_deliver_success', 'local_evokegame', $delivered)
        ];
    }

    /**
     * Deliver skill points return fields
     *
     * @return external_single_structure
     */
    public static function deliver_points_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_TEXT, 'Return message')
            )
        );
    }
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
