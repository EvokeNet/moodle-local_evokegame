<?php

/**
 * Evoke game main renderer
 *
 * @package     local_evokegame
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokegame\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;

class renderer extends plugin_renderer_base {
    public function render_scoreboard(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('local_evokegame/scoreboard', $data);
    }

    public function render_dashboard(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('local_evokegame/dashboard', $data);
    }
}