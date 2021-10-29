<?php

/**
 * Manage activity modules custom fields
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_evokegame');

$output = $PAGE->get_renderer('core_customfield');

$handler = local_evokegame\customfield\mod_handler::create();

$outputpage = new \core_customfield\output\management($handler);

echo $output->header();

echo $output->heading(new lang_string('pluginname', 'local_evokegame'));

echo $output->render($outputpage);

echo $output->footer();
