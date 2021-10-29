<?php

/**
 * Evoke game events observers
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\local_evokegame\observers\modulecompleted::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_evokeportfolio\event\submission_sent',
        'callback' => '\local_evokegame\observers\submissionsent::observer',
        'internal' => false
    ],
    [
        'eventname' => '\core\event\user_graded',
        'callback' => '\local_evokegame\observers\usergraded::observer',
        'internal' => false
    ],
];
