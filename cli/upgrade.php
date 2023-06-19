<?php

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_once($CFG->libdir.'/clilib.php');

$help = "Upgrade plugin features\n";

list($options, $unrecognized) = cli_get_params(
    ['help' => false, 'migration' => 'evocoins'],
    ['h' => 'help', 'm' => 'migration'],
);

if ($options['help']) {
    echo $help . PHP_EOL;

    exit(0);
}

if (!$options['migration']) {
    echo "You need to inform a migration to perform" . PHP_EOL;

    exit(0);
}

$migration = new \local_evokegame\temp_migration\migration();

if ($options['migration'] == 'evocoins') {
    $migration->migrate_courses();
}

if ($options['migration'] == 'skills') {
    $migration->migrate_courses('skills');
}

if ($options['migration'] == 'skillsusers') {
    $migration->migrate_courses('skillsusers');
}

if ($options['migration'] == 'badgescriterias') {
    $migration->migrate_courses('badgescriterias');
}

exit(0);