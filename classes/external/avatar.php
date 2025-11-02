<?php

namespace local_evokegame\external;

use core\context;
use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;

/**
 * Avatar external api class.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class avatar extends external_api {
    /**
     * Choose avatar parameters
     *
     * @return external_function_parameters
     */
    public static function choose_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'avatarid' => new external_value(PARAM_INT, 'The avatar id'),
        ]);
    }

    /**
     * Choose avatar method
     *
     * @param int $contextid
     * @param int $avatarid
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function choose($contextid, $avatarid) {
        global $CFG;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::choose_parameters(),
            ['contextid' => $contextid, 'avatarid' => $avatarid]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        set_user_preference('evokegame_avatarid', $avatarid);

        $avatarurl = $CFG->wwwroot . '/local/evokegame/pix/a' . $avatarid . '.svg';

        return [
            'status' => 'ok',
            'message' => get_string('chooseavatar_success', 'local_evokegame'),
            'data' => json_encode($avatarurl)
        ];
    }

    /**
     * Choose avatar return fields
     *
     * @return external_single_structure
     */
    public static function choose_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }
}
