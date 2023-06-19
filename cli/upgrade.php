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
    echo "Starting Evocoins Migration" . PHP_EOL;
    $migration->migrate_courses();
    echo "Migration Finished" . PHP_EOL;
}

if ($options['migration'] == 'skills') {
    echo "Starting Skills Migration" . PHP_EOL;
    $migration->migrate_courses('skills');
    echo "Migration Finished" . PHP_EOL;
}

if ($options['migration'] == 'skillsusers') {
    echo "Starting Skills Users Migration" . PHP_EOL;
    $migration->migrate_courses('skillsusers');
    echo "Migration Finished" . PHP_EOL;
}

if ($options['migration'] == 'badgescriterias') {
    echo "Starting Badges Criterias Migration" . PHP_EOL;
    $migration->migrate_courses('badgescriterias');
    echo "Migration Finished" . PHP_EOL;
}

exit(0);