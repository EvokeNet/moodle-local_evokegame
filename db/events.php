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
        'callback' => '\local_evokegame\observers\evokeportfolio\submissionsent::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_portfoliobuilder\event\entry_added',
        'callback' => '\local_evokegame\observers\portfoliobuilder\entryadded::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_portfoliogroup\event\entry_added',
        'callback' => '\local_evokegame\observers\portfoliogroup\entryadded::observer',
        'internal' => false
    ],
    [
        'eventname' => '\core\event\user_graded',
        'callback' => '\local_evokegame\observers\usergraded::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_evokeportfolio\event\comment_added',
        'callback' => '\local_evokegame\observers\evokeportfolio\mentor::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_portfoliobuilder\event\comment_added',
        'callback' => '\local_evokegame\observers\portfoliobuilder\commentadded::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_portfoliogroup\event\comment_added',
        'callback' => '\local_evokegame\observers\portfoliogroup\commentadded::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_evokeportfolio\event\like_sent',
        'callback' => '\local_evokegame\observers\evokeportfolio\mentor::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_portfoliobuilder\event\like_sent',
        'callback' => '\local_evokegame\observers\portfoliobuilder\likesent::observer',
        'internal' => false
    ],
    [
        'eventname' => '\mod_portfoliogroup\event\like_sent',
        'callback' => '\local_evokegame\observers\portfoliogroup\likesent::observer',
        'internal' => false
    ],
    [
        'eventname' => '\local_evokegame\event\points_added',
        'callback' => '\local_evokegame\observers\badgeissuer::observer',
        'internal' => false
    ],
    [
        'eventname' => '\local_evokegame\event\evocoins_added',
        'callback' => '\local_evokegame\observers\badgeissuer::observer',
        'internal' => false
    ],
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => '\local_evokegame\observers\badgeissuer::observer',
        'internal' => false
    ],
];
