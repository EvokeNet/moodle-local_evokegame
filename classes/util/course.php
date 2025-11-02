<?php

namespace local_evokegame\util;

use stdClass;
use core_course_list_element;
use moodle_url;

class course {
    /**
     * Returns the first course's summary issue
     *
     * @param $course
     * @param $courselink
     *
     * @return string
     *
     * @throws \moodle_exception
     */
    public function get_summary_image_url($course, $context) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($context->id,
            'local_evokegame',
            'scoreboard_image',
            $course->id,
            'timemodified',
            false);

        if ($files) {
            $scoreboardimage = current($files);
            $path = [
                '',
                $scoreboardimage->get_contextid(),
                $scoreboardimage->get_component(),
                $scoreboardimage->get_filearea(),
                $course->id . $scoreboardimage->get_filepath() . $scoreboardimage->get_filename()
            ];

            $fileurl = \moodle_url::make_file_url('/pluginfile.php', implode('/', $path), true);

            return $fileurl->out();
        }

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        foreach ($course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $pathcomponents = [
                    '/pluginfile.php',
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename()
                ];

                $path = implode('/', $pathcomponents);

                return (new moodle_url($path))->out();
            }
        }

        return (new moodle_url('/local/evokegame/pix/default_course.jpg'))->out();
    }
}