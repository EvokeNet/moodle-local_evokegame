<?php

/**
 * This adds the custom fields management page.
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // Needs this condition or there is error on login page.
    $ADMIN->add('localplugins',
        new admin_externalpage('local_evokegame', new lang_string('customfields', 'local_evokegame'),
            $CFG->wwwroot . '/local/evokegame/customfield.php',
            array('moodle/course:configurecustomfields')
        )
    );
}
