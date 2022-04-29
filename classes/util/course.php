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
    public function get_summary_image_url($course) {
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