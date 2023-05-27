<?php

namespace local_evokegame\temp_migration;

class skills_submission {
    protected $course;
    protected $coursemoduleswithskills;

    public function __construct($course, $coursemoduleswithskills) {
        $this->course = $course;
        $this->coursemoduleswithskills = $coursemoduleswithskills;
    }

    public function migrate_skills() {
        $this->migrate_submission_skills();

        $this->migrate_comment_skills();

        $this->migrate_like_skills();

        $this->migrate_grading_skills();
    }
    private function migrate_submission_skills() {
        $coursemoduleswithsubmissionskills = $this->extract_skills('submission_');

        if ($coursemoduleswithsubmissionskills) {
            $this->insert_skills_in_course($coursemoduleswithsubmissionskills, 'submission');
        }
    }

    private function migrate_comment_skills() {
        $coursemoduleswithcommentskills = $this->extract_skills('comment_');

        if ($coursemoduleswithcommentskills) {
            $this->insert_skills_in_course($coursemoduleswithcommentskills, 'comment');
        }
    }

    private function migrate_like_skills() {
        $coursemoduleswithlikeskills = $this->extract_skills('like_');

        if ($coursemoduleswithlikeskills) {
            $this->insert_skills_in_course($coursemoduleswithlikeskills, 'like');
        }
    }

    private function migrate_grading_skills() {
        $coursemoduleswithgradingskills = $this->extract_skills('grading_');

        if ($coursemoduleswithgradingskills) {
            $this->insert_skills_in_course($coursemoduleswithgradingskills, 'grading');
        }
    }

    private function insert_skills_in_course($coursemoduleswithsubmissionskills, $action) {
        foreach ($coursemoduleswithsubmissionskills as $cmid => $coursemodule) {
            foreach ($coursemodule as $skillslug => $value) {
                $skillname = str_replace('_', ' ', $skillslug);
                $skillname = ucfirst($skillname);

                $skill = $this->first_or_create($skillname);

                $this->insert_skill_in_module($skill->id, $cmid, $value, $action, $skillslug);
            }
        }
    }

    private function first_or_create($skillname) {
        global $DB;

        if ($skill = $this->is_skill_in_course($skillname)) {
            return $skill;
        }

        $skill = new \stdClass();
        $skill->courseid = $this->course->id;
        $skill->name = $skillname;
        $skill->timecreated = time();
        $skill->timemodified = time();

        $skillid = $DB->insert_record('evokegame_skills', $skill);

        $skill->id = $skillid;

        return $skill;
    }

    private function is_skill_in_course($skillname) {
        global $DB;

        return $DB->get_record('evokegame_skills', [
            'courseid' => $this->course->id,
            'name' => $skillname
        ]);
    }

    private function insert_skill_in_module(int $skillid, int $cmid, int $value, string $action, string $skillslug) {
        global $DB;

        $skill = new \stdClass();
        $skill->skillid = $skillid;
        $skill->cmid = $cmid;
        $skill->value = $value;
        $skill->action = $action;
        $skill->skillslug = $skillslug;
        $skill->timecreated = time();
        $skill->timemodified = time();

        $skillid = $DB->insert_record('evokegame_skills_modules', $skill);

        $skill->id = $skillid;

        return $skill;
    }

    private function extract_skills($skillprefix) {
        $data = [];

        foreach ($this->coursemoduleswithskills as $cmid => $skills) {
            $prefixlen = strlen($skillprefix);

            foreach ($skills as $key => $value) {
                if (substr($key, 0, $prefixlen) != $skillprefix) {
                    continue;
                }

                $submissionskill = substr($key, $prefixlen);

                $data[$cmid][$submissionskill] = $value;
            }

        }

        return $data;
    }
}
