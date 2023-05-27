<?php

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_once($CFG->libdir.'/clilib.php');

$help = "Upgrade plugin features\n";

list($options, $unrecognized) = cli_get_params(
    ['help' => false, 'migration' => 'evocoins'],
    ['h' => 'help', 'm' => 'migration'],
);

echo $options['migration'];

if ($options['help']) {
    echo $help . PHP_EOL;

    exit(0);
}

if (!$options['migration']) {
    echo "You need to inform a migration to perform" . PHP_EOL;

    exit(0);
}

$migration = new \local_evokegame\temp_migration\migration();

$migration->migrate_courses($options['migration']);

exit(0);