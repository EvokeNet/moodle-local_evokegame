<?php

namespace local_evokegame\temp_migration;

class evocoins {
    protected $coursemoduleswithskills;

    public function __construct($coursemoduleswithskills) {
        $this->coursemoduleswithskills = $coursemoduleswithskills;
    }

    public function migrate_evocoins() {
        $onlyevocoins = $this->extract_evocoins_fields();

        if ($onlyevocoins) {
            $this->insert_evokegame_evcs_modules($onlyevocoins);
        }
    }

    private function extract_evocoins_fields() {
        $data = [];

        foreach ($this->coursemoduleswithskills as $cmid => $skills) {
            if (array_key_exists('evocoins', $skills)) {
                $data[$cmid] = $skills['evocoins'];
            }
        }

        return $data;
    }

    private function insert_evokegame_evcs_modules($onlyevocoins) {
        global $DB;

        $data = [];

        foreach ($onlyevocoins as $key => $onlyevocoin) {
            $data[] = [
                'cmid' => $key,
                'value' => $onlyevocoin,
                'timecreated' => time(),
                'timemodified' => time(),
            ];
        }

        $DB->insert_records('evokegame_evcs_modules', $data);
    }
}
