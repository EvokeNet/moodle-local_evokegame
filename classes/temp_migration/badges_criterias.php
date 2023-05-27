<?php

namespace local_evokegame\temp_migration;

class badges_criterias {
    public function migrate_criterias() {
        $this->migrate_skillpoints_method();

        $this->migrate_skillpointsaggregation_method();
    }

    public function migrate_skillpoints_method() {
        global $DB;

        $criterias = $DB->get_records('evokegame_badges_criterias', ['method' => 'skillpoints']);

        foreach ($criterias as $criteria) {
            $sql = 'SELECT DISTINCT s.*
                    FROM {evokegame_skills_modules} m
                    INNER JOIN {evokegame_skills} s ON s.id = m.skillid
                    WHERE s.courseid = :courseid AND m.skillslug = :skillslug';

            $skill = $DB->get_record_sql($sql, [
                'courseid' => $criteria->courseid,
                'skillslug' => $criteria->target
            ]);

            if (!$skill) {
                continue;
            }

            $criteria->target = $skill->id;
            $criteria->timemodified = time();

            $DB->update_record('evokegame_badges_criterias', $criteria);
        }
    }

    public function migrate_skillpointsaggregation_method() {
        global $DB;

        $criterias = $DB->get_records('evokegame_badges_criterias', ['method' => 'skillpointsaggregation']);

        foreach ($criterias as $criteria) {
            $target = explode(',', $criteria->target);

            foreach ($target as $key => $targetskill) {
                $sql = 'SELECT DISTINCT s.*
                    FROM {evokegame_skills_modules} m
                    INNER JOIN {evokegame_skills} s ON s.id = m.skillid
                    WHERE s.courseid = :courseid AND m.skillslug = :skillslug';

                $skill = $DB->get_record_sql($sql, [
                    'courseid' => $criteria->courseid,
                    'skillslug' => $targetskill
                ]);

                if (!$skill) {
                    continue;
                }

                $target[$key] = $skill->id;
            }


            $criteria->target = implode(',', $target);
            $criteria->timemodified = time();

            $DB->update_record('evokegame_badges_criterias', $criteria);
        }
    }
}
