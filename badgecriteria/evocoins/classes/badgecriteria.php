<?php

/**
 * This file contains the evokegame element evocoins's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace evokegamebadgecriteria_evocoins;

use local_evokegame\util\evocoin;

defined('MOODLE_INTERNAL') || die();

/**
 * The evokegame element evocoins's core interaction API.
 *
 * @package     local_evokegame
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_evokegame\badgecriteria {
    public function user_achieved_criteria(): bool {
        $evocoinutil = new evocoin($this->userid);
        $coins = $evocoinutil->get_coins();

        $requiredcoins = (int) $this->badgecriteria->value;
        if ($coins >= $requiredcoins) {
            return true;
        }

        return false;
    }

    public function get_user_criteria_progress(): int {
        $evocoinutil = new evocoin($this->userid);
        $coins = $evocoinutil->get_coins();

        if ($coins == 0) {
            return 0;
        }

        $requiredcoins = (int) $this->badgecriteria->value;
        if ($coins >= $requiredcoins) {
            return 100;
        }

        return (int)($coins * 100 / $this->badgecriteria->value);
    }

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'evokegamebadgecriteria_evocoins');

        $progress = $this->get_user_criteria_progress();

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'evokegamebadgecriteria_evocoins', $this->badgecriteria->value);

        return '<p class="mb-0">'.$pluginname.'
                        <a class="btn btn-link p-0"
                           role="button"
                           data-container="body"
                           data-toggle="popover"
                           data-placement="right"
                           data-html="true"
                           tabindex="0"
                           data-trigger="focus"
                           data-content="<div class=\'no-overflow\'><p>'.$criteriaprogresdesc.'</p></div>">
                            <i class="icon fa fa-info-circle text-info fa-fw " title="'.$pluginname.'" role="img" aria-label="'.$pluginname.'"></i>
                        </a>
                    </p>
                    <div class="progress ml-0">
                        <div class="progress-bar" role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100">'.$progress.'%</div>
                    </div>';
    }
}
