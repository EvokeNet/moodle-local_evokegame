<?php

namespace local_evokegame\util\report;

class portfolio {
    public function get_course_total_likes($courseid) {
        global $DB;

        $sql = 'SELECT count(e.id)
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder_reactions} r ON r.entryid = e.id
                WHERE e.courseid = :courseid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid]);
    }

    public function get_course_total_comments($courseid) {
        global $DB;

        $sql = 'SELECT count(e.id)
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder_comments} c ON c.entryid = e.id
                WHERE e.courseid = :courseid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid]);
    }

    public function get_course_total_entries($courseid) {
        global $DB;

        $sql = 'SELECT count(e.id)
                FROM {portfoliobuilder_entries} e
                WHERE e.courseid = :courseid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid]);
    }

    public function get_course_total_entries_by_chapter($courseid) {
        global $DB;

        $sql = 'SELECT chapter, count(chapter) as qtd
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder} p ON p.id = e.portfolioid
                WHERE e.courseid = :courseid
                GROUP BY p.chapter';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $chapter = get_string('chapter', 'mod_portfoliobuilder') . ' ' . $record->chapter;

            $data[$chapter] = $record->qtd;
        }

        return $data;
    }

    public function get_course_total_likes_by_chapter($courseid) {
        global $DB;

        $sql = 'SELECT chapter, count(chapter) as qtd
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder_reactions} r ON r.entryid = e.id
                INNER JOIN {portfoliobuilder} p ON p.id = e.portfolioid
                WHERE e.courseid = :courseid
                GROUP BY p.chapter';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $chapter = get_string('chapter', 'mod_portfoliobuilder') . ' ' . $record->chapter;

            $data[$chapter] = $record->qtd;
        }

        return $data;
    }

    public function get_course_total_comments_by_chapter($courseid) {
        global $DB;

        $sql = 'SELECT chapter, count(chapter) as qtd
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder_comments} c ON c.entryid = e.id
                INNER JOIN {portfoliobuilder} p ON p.id = e.portfolioid
                WHERE e.courseid = :courseid
                GROUP BY p.chapter';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        $data = [];
        foreach ($records as $record) {
            $chapter = get_string('chapter', 'mod_portfoliobuilder') . ' ' . $record->chapter;

            $data[$chapter] = $record->qtd;
        }

        return $data;
    }
}
