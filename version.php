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
 * Version metadata for the block_pluginname plugin.
 *
 * @package   block_course_completion_stats
 * @copyright 2024, Muhammad Arsalan arsalan.fonerep@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2024100301;
$plugin->requires = 2024042203;
$plugin->component = 'block_course_completion_stats';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1';

// $plugin->dependencies = [
//     'mod_forum' => 2022042100,
//     'mod_data' => 2022042100
// ];