<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Live status renderable.
 *
 * @package   block_cb_site_monitor
 */

namespace block_course_completion_stats\output;

defined('MOODLE_INTERNAL') || die();


use renderable;
use templatable;
use renderer_base;
use block_course_completion_stats\usage;
use block_course_completion_stats\utility;

/**
 * Renderable for live status tab
 *
 */
class course implements renderable, templatable {
    public function export_for_template(renderer_base $output){
        $usage = usage::get_instance();
        $course = $usage->get_courses();
        $data['course_name']=$course->fullname;
        
        $output = null;
        return $data;
    }
}