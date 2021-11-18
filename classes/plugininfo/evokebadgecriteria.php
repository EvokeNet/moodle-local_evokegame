<?php

/**
 * Evoke game subplugin class
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();

class evokebadgecriteria extends base {
    public function is_uninstall_allowed() {
        return false;
    }
}
