<?php

namespace local_evokegame\temp_migration;

class skills_users {
    public function migrate_skills_users() {
        global $DB;

        $logs = $DB->get_records('evokegame_logs');

        foreach ($logs as $log) {
            $skillmodule = $DB->get_record('evokegame_skills_modules', [
                'cmid' => $log->sourceid,
                'action' => $log->pointsourcetype,
                'skillslug' => $log->skill
            ]);

            if (!$skillmodule) {
                continue;
            }

            $data = new \stdClass();
            $data->skillmoduleid = $skillmodule->id;
            $data->userid = $log->userid;
            $data->value = $log->points;
            $data->timecreated = time();
            $data->timemodified = time();

            $DB->insert_record('evokegame_skills_users', $data);
        }
    }
}
