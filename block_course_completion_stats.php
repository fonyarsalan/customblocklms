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
 * Block definition class for the block_course_completion_stats plugin.
 *
 * @package   block_course_completion_stats
 * @copyright 2024, Muhammad Arsalan arsalan.fonerep@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/completion/classes/progress.php');
require_once($CFG->libdir . '/completionlib.php');


class block_course_completion_stats extends block_base {

    /**
     * Indicates API features that the forum supports.
    *
    * @param string $feature
    * @return null|bool
    */
    function block_course_completion_stats_supports(string $feature): bool {
        switch($feature) {
            case FEATURE_COMPLETION_TRACKS_VIEWS:
                return true;
            case FEATURE_COMPLETION_HAS_RULES:
                return true;
            default:
                return null;
        }
    }   
    
    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('course_completion_stats', 'block_course_completion_stats');
    }

    public function specialization() {
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_course_completion_stats');
    }

    /**
     * Gets the block contents.
     *
     * @return string The block HTML.
     */
    public function get_content() {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        // Fetch categories and courses
        $categories = $this->get_all_categories_with_courses();

        // Prepare data for the Mustache template
        $data = [
            'categories' => $categories,
        ];

        // Render the Mustache template
        $this->content->text = $OUTPUT->render_from_template('block_course_completion_stats/content', $data);

        return $this->content;
    }

    /**
     * Fetch all categories and their associated courses.
     *
     * @return array
     */
    private function get_all_categories_with_courses() {
        global $DB, $USER;
    
        // Fetch all categories
        $categories = $DB->get_records('course_categories', null, 'name ASC', 'id, name');
    
        // Prepare an array to hold categories with their courses
        $categories_with_courses = [];
    
        // Iterate through each category and fetch its courses
        foreach ($categories as $category) {
            // Fetch courses belonging to this category
            $courses = $DB->get_records('course', ['category' => $category->id], 'fullname ASC', 'id, fullname');
            // Prepare an array of course names for the current category
            $course_list = [];
            foreach ($courses as $course) {

                $courseProgress = progress::get_course_progress_percentage($course->id, $USER->id);
                $context = \context_course::instance($course->id);
                $isEnrolled = is_enrolled($context, $USER->id);
 
                print_r($courseProgress);
                $course_list[] = [
                    'fullname' => $course->fullname, // Use 'fullname' property directly
                    'id' => $course->id, 
                    'progress'=>$courseProgress,
                    'isenrolled' => $isEnrolled
                ];
            }
    
            // Add to the categories array
            $categories_with_courses[] = [
                'name' => $category->name,
                'courses' => $course_list, // Update to use formatted course list
            ];
        }
    
        return $categories_with_courses;
    }
    
    
    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'course-view' => true, // Only show in course pages
            'my-index' => true,
            'site-index' => false, // No appearance on the dashboard or site index
            'mod' => false, // Not needed for activity modules
            'my' => true // Disable block on the dashboard (my page)
        ];
    }
    
    public function instance_allow_multiple() {
        return false; // Only one instance of the block allowed
    }
    
    public function after_block_instance_created() {
        global $DB, $PAGE;

        if ($PAGE->pagelayout == 'course') {
            // Find the block instance in the current course
            $block = $DB->get_record('block_instances', ['blockname' => $this->instance->blockname]);

            if ($block) {
                // Set the region to 'side-post' (right-side drawer)
                $block->defaultregion = 'side-post';
                $DB->update_record('block_instances', $block);
            }
        }
    }   
}

class progress extends \core_completion\progress {
    
    public static function get_course_progress_percentage($course, $userid = 0) {
        global $USER;
    
        if (isloggedin()) {
            $userid = $USER->id;
        } else {
            error_log("User is not logged in");
            return 0;
        }
    
        if (is_numeric($course)) {
            // If the course is passed as an ID, fetch the course object.
            $course = get_course($course);
        }
    
        if (empty($course)) {
            error_log("Course object is empty!");
            return 0;
        }
    
        $completion = new \completion_info($course);
    
        // Ensure completion is enabled.
        if (!$completion->is_enabled()) {
            return null;
        }
    
        if (!$completion->is_tracked_user($userid)) {
            return null;
        }
    
        // Check if the course has been completed by the user.
        if ($completion->is_course_complete($userid)) {
            return 100;
        }
    
        // Get the number of modules that support completion.
        $modules = $completion->get_activities();
        $count = count($modules);
        if (!$count) {
            return 0;
        }
    
        // Get the number of completed modules.
        $completed = 0;
        foreach ($modules as $module) {
            try {
                $data = $completion->get_data($module, true, $userid);
            } catch (Exception $e) {
                return $e->getMessage();
            }
            if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) {
                $completed++;
            }
        }
    
        return ($completed / $count) * 100;
    }
    
}