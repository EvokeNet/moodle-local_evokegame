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

    public function render_dashboardnavbar(renderable $page) {
        try {
            $data = $page->export_for_template($this);

            debugging("[evokegame] render_dashboardnavbar: data keys: " . implode(', ', array_keys($data)), DEBUG_NORMAL);

            $result = parent::render_from_template('local_evokegame/dashboardnavbar', $data);

            debugging("[evokegame] render_dashboardnavbar: rendered length: " . strlen($result ?? ''), DEBUG_NORMAL);

            return $result;
        } catch (\Exception $e) {
            debugging("[evokegame] Error in render_dashboardnavbar: " . $e->getMessage(), DEBUG_NORMAL);
            return '';
        }
    }
}