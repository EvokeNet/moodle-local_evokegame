<?php

namespace local_evokegame\util\report;

class portfolio {
    private function has_portfolio_tables(): bool {
        global $DB;
        $dbman = $DB->get_manager();

        $entries = new \xmldb_table('portfoliobuilder_entries');
        $portfolio = new \xmldb_table('portfoliobuilder');
        $reactions = new \xmldb_table('portfoliobuilder_reactions');
        $comments = new \xmldb_table('portfoliobuilder_comments');

        return $dbman->table_exists($entries)
            && $dbman->table_exists($portfolio)
            && $dbman->table_exists($reactions)
            && $dbman->table_exists($comments);
    }

    public function get_course_total_likes($courseid) {
        global $DB;

        if (!$this->has_portfolio_tables()) {
            return 0;
        }

        $sql = 'SELECT count(e.id)
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder_reactions} r ON r.entryid = e.id
                WHERE e.courseid = :courseid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid]);
    }

    public function get_course_total_comments($courseid) {
        global $DB;

        if (!$this->has_portfolio_tables()) {
            return 0;
        }

        $sql = 'SELECT count(e.id)
                FROM {portfoliobuilder_entries} e
                INNER JOIN {portfoliobuilder_comments} c ON c.entryid = e.id
                WHERE e.courseid = :courseid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid]);
    }

    public function get_course_total_entries($courseid) {
        global $DB;

        if (!$this->has_portfolio_tables()) {
            return 0;
        }

        $sql = 'SELECT count(e.id)
                FROM {portfoliobuilder_entries} e
                WHERE e.courseid = :courseid';

        return $DB->count_records_sql($sql, ['courseid' => $courseid]);
    }

    public function get_course_total_entries_by_chapter($courseid) {
        global $DB;

        if (!$this->has_portfolio_tables()) {
            return false;
        }

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

        if (!$this->has_portfolio_tables()) {
            return false;
        }

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

        if (!$this->has_portfolio_tables()) {
            return false;
        }

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
