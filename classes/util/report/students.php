<?php

namespace local_evokegame\util\report;

class students {
    public function get_course_total($context) {
        global $DB;

        $sql = 'SELECT DISTINCT u.id, u.firstname, u.lastname, u.email';

        $capjoin = get_enrolled_with_capabilities_join($context, '', 'mod/portfoliobuilder:submit');

        $sql .= ' FROM {user} u ' . $capjoin->joins;

        $sql .= ' WHERE 1 = 1 AND ' . $capjoin->wheres;

        $params = $capjoin->params;

        $records = $DB->get_records_sql($sql, $params);

        if (!$records) {
            return false;
        }

        return count($records);
    }
}
