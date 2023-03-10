<?php

namespace local_evokegame\external;

use context;
use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;
use local_evokegame\forms\badgecriteria as badgecriteriaform;
use local_evokegame\util\skill;
use local_evokegame\util\badgecriteria as badgecriteriautil;

/**
 * Badge criteria external api class.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends external_api {
    /**
     * Create badge parameters
     *
     * @return external_function_parameters
     */
    public static function create_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'courseid' => new external_value(PARAM_INT, 'The course id'),
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

        $mform = new badgecriteriaform($data, $data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $now = time();

        $skillutil = new skill();

        $badgecriteria = new \stdClass();
        $badgecriteria->courseid = $courseid;
        $badgecriteria->evokebadgeid = $validateddata->badgeid;
        $badgecriteria->method = $validateddata->method;
        $badgecriteria->value = $validateddata->value;
        $badgecriteria->timecreated = $now;
        $badgecriteria->timemodified = $now;

        if ($validateddata->skilltarget && $validateddata->method == 'skillpoints') {
            $badgecriteria->target = $skillutil->get_skill_string_name($courseid, $validateddata->skilltarget);
        }

        if (!empty($validateddata->skilltargetaggregation) && $validateddata->method == 'skillpointsaggregation') {
            $targets = [];
            foreach ($validateddata->skilltargetaggregation as $item) {
                $targets[] = $skillutil->get_skill_string_name($courseid, $item);
            }
            $badgecriteria->target = implode(',', $targets);
        }

        $badgecriteriaid = $DB->insert_record('evokegame_badges_criterias', $badgecriteria);

        $badgecriteria->id = $badgecriteriaid;

        return [
            'status' => 'ok',
            'message' => get_string('createbadgecriteria_success', 'local_evokegame'),
            'data' => json_encode($badgecriteria)
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
     * Delete badge parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'badgecriteria' => new external_single_structure([
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
    public static function delete($badgecriteria) {
        global $DB;

        self::validate_parameters(self::delete_parameters(), ['badgecriteria' => $badgecriteria]);

        $badgecriteria = (object)$badgecriteria;

        $DB->delete_records('evokegame_badges_criterias', ['id' => $badgecriteria->id]);

        return [
            'status' => 'ok',
            'message' => get_string('deletebadgecriteria_success', 'local_evokegame')
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