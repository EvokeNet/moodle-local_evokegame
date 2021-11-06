<?php

/**
 * Evokeportfolio services definition
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_evokegame_chooseavatar' => [
        'classname' => 'local_evokegame\external\avatar',
        'classpath' => 'local/evokegame/classes/external/avatar.php',
        'methodname' => 'choose',
        'description' => 'User can choose an avatar',
        'type' => 'write',
        'ajax' => true
    ]
];
