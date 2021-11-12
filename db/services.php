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
    'local_evokegame_createsuperpower' => [
        'classname' => 'local_evokegame\external\superpower',
        'classpath' => 'local/evokegame/classes/external/superpower.php',
        'methodname' => 'create',
        'description' => 'Creates a new superpower',
        'type' => 'write',
        'ajax' => true
    ],
    'local_evokegame_editsuperpower' => [
        'classname' => 'local_evokegame\external\superpower',
        'classpath' => 'local/evokegame/classes/external/superpower.php',
        'methodname' => 'edit',
        'description' => 'Creates a new superpower',
        'type' => 'write',
        'ajax' => true
    ],
    'local_evokegame_deletesuperpower' => [
        'classname' => 'local_evokegame\external\superpower',
        'classpath' => 'local/evokegame/classes/external/superpower.php',
        'methodname' => 'delete',
        'description' => 'Creates a new superpower',
        'type' => 'write',
        'ajax' => true
    ],
];
