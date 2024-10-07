<?php

namespace block_course_completion_stats\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;

class renderer extends plugin_renderer_base {
    public function render_course_context(course $course){
        return $this->render_from_template('block_course_completion_stats/course',
        $get_course_completion->export_for_template($this));
    }
}