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
 * Post-install code for the submission_forum module.
 *
 * @package assignsubmission_forum
 * @copyright 2012 Massey University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


/**
 * Code run after the assignsubmission_forum module database tables have been created.
 * Moves the plugin to the bottom of the list (of 4)
 * @return bool
 */
function xmldb_assignsubmission_forum_install() {
    global $CFG;

    // do the install

    require_once($CFG->dirroot . '/mod/assign/adminlib.php');
    // set the correct initial order for the plugins
    $pluginmanager = new assign_plugin_manager('assignsubmission');

    $pluginmanager->move_plugin('forum', 'down');
    $pluginmanager->move_plugin('forum', 'down');
    $pluginmanager->move_plugin('forum', 'down');

    // do the upgrades
    return true;



}


