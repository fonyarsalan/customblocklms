<?php

namespace block_course_completion_stats;

use context_system;

class usage {
    
    /**
     * Static $instance to implement singletone class
     * @var block_cb_site_monitor_usage
     */
    public static $singleinstance = null;

     /**
     * Get instance of class using this method
     *
     * @return block_cb_site_monitor_usage
     */
    public static function get_instance(){
        if(self::$singleinstance == null){
            self::$singleinstance = new usage();
        }
        return self::$singleinstance;
    }

    public function get_course(){
        global $DB;

        // Fetch all courses from the database
        $courses = $DB->get_records('course', null, 'fullname ASC', 'id, fullname, category');
        return $courses;
    }

    public function get_course_category(){
        global $DB;

        // Fetch all categories from the database
        $categories = $DB->get_records('course_categories', null, 'name ASC', 'id, name');
        return $categories; 
    }
}