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
    ],
    'local_evokegame_createbadge' => [
        'classname' => 'local_evokegame\external\badge',
        'classpath' => 'local/evokegame/classes/external/badge.php',
        'methodname' => 'create',
        'description' => 'Creates a new badge',
        'type' => 'write',
        'ajax' => true
    ],
    'local_evokegame_editbadge' => [
        'classname' => 'local_evokegame\external\badge',
        'classpath' => 'local/evokegame/classes/external/badge.php',
        'methodname' => 'edit',
        'description' => 'Creates a new badge',
        'type' => 'write',
        'ajax' => true
    ],
    'local_evokegame_deletebadge' => [
        'classname' => 'local_evokegame\external\badge',
        'classpath' => 'local/evokegame/classes/external/badge.php',
        'methodname' => 'delete',
        'description' => 'Deletes a badge',
        'type' => 'write',
        'ajax' => true
    ],
    'local_evokegame_createbadgecriteria' => [
        'classname' => 'local_evokegame\external\badgecriteria',
        'classpath' => 'local/evokegame/classes/external/badgecriteria.php',
        'methodname' => 'create',
        'description' => 'Creates a new badge criteria',
        'type' => 'write',
        'ajax' => true
    ],
    'local_evokegame_deletebadgecriteria' => [
        'classname' => 'local_evokegame\external\badgecriteria',
        'classpath' => 'local/evokegame/classes/external/badgecriteria.php',
        'methodname' => 'delete',
        'description' => 'Deletes a badge',
        'type' => 'write',
        'ajax' => true
    ],
];
