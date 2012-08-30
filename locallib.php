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
 * This file contains the definition for the library class for forum submission plugin
 *
 *
 *
 * @package assignsubmission_forum
 * @copyright 2012 Massey University  {@link http://www.massey.ac.nz}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Include eventslib.php */
require_once($CFG->libdir.'/eventslib.php');

defined('MOODLE_INTERNAL') || die();
/**
 * File areas for file submission assignment
 */
define('ASSIGNSUBMISSION_FORUM_MAXFILES', 20);
define('ASSIGNSUBMISSION_FORUM_MAXSUMMARYFILES', 5);
define('ASSIGNSUBMISSION_FORUM_FILEAREA', 'submission_files');


/**
 * library class for file submission plugin extending submission plugin base class
 *
 * @package   assignsubmission_forum
 * @copyright 2012 Massey Univerity
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_forum extends assign_submission_plugin {

    /**
     * Get the name of the forum submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('forum', 'assignsubmission_forum');
    }


    /**
     * Get the default setting for forum submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */

    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE, $DB;

        // getting the values previously selected from db
        $defaultextractiondate = $this->get_config('extractiondate');
        $defaultforumidselected = $this->get_config('forumidselected');

        // Adding the date/time selection onto the form
        $mform->addElement('date_time_selector', 'assignsubmission_forum_extractiondate', get_string('extractiondate', 'assignsubmission_forum'));
        $mform->addHelpButton('assignsubmission_forum_extractiondate', 'extractiondate', 'assignsubmission_forum');
        $mform->setDefault('assignsubmission_forum_extractiondate', time());
        $mform->disabledIf('assignsubmission_forum_extractiondate', 'assignsubmission_forum_enabled', 'eq', 0);

        // selecting the forums belonging to the course for display as options
        // each choice is an array entry, with forum id as value and forum name as display string
        // EH: I assume it will be ok to display all forums belonging to a course, regardless of things like groupings?
        $choices = array();
        $forums = $DB->get_records('forum', array('course'=>$COURSE->id));
        // EH: do we need to control the display order? (probably not, should be in array index order which is id order?)
        foreach ($forums as $forum) {
            $choices[$forum->id] = $forum->name;
        }

        $mform->addElement('select', 'assignsubmission_forum_forumidselected', get_string('forumidselected', 'assignsubmission_forum'), $choices);
        $mform->addHelpButton('assignsubmission_forum_forumidselected', 'forumidselected', 'assignsubmission_forum');
        $mform->setDefault('assignsubmission_forum_forumidselected', $defaultforumidselected);
        $mform->disabledIf('assignsubmission_forum_forumidselected', 'assignsubmission_forum_enabled', 'eq', 0);

    }

    /**
     * Save the settings for file submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        global $DB;

        // This is only called when 'Extract from forum(s)' is enabled
        $this->set_config('extractiondate', $data->assignsubmission_forum_extractiondate);
        $this->set_config('forumidselected', $data->assignsubmission_forum_forumidselected);

        // set extracted to false so it is run next time when cron picks it up
        $this->set_config('extracted', false);

        $temp = $this->assignment->get_instance()->id;

        return true;
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_forum', ASSIGNSUBMISSION_FORUM_FILEAREA, $submission->id, "timemodified", false);

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }


    /**
     * Count the number of files
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {

        $fs = get_file_storage();

        // get context id of assignment (via the course module)
        $cm = get_coursemodule_from_instance('assign', $this->assignment->get_instance()->id, $this->assignment->get_course()->id);
        $contextid_assignment = context_module::instance($cm->id)->id;
        $files = $fs->get_area_files($contextid_assignment, 'assignsubmission_forum', $area, $submissionid, "id", false);
        return count($files);
    }

    /**
     * Get file submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_file_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_forum', array('submission'=>$submissionid));
    }


    /**
     * Display the list of files  in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_FORUM_FILEAREA);

        $showviewlink = $count > ASSIGNSUBMISSION_FORUM_MAXSUMMARYFILES;
        if ($count <= ASSIGNSUBMISSION_FORUM_MAXSUMMARYFILES) {
            return $this->assignment->render_area_files('assignsubmission_forum', ASSIGNSUBMISSION_FORUM_FILEAREA, $submission->id);
        } else {
            return get_string('countfiles', 'assignsubmission_forum', $count);
        }
    }

    /**
     * No full submission view - the summary contains the list of files and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        return $this->assignment->render_area_files('assignsubmission_forum', ASSIGNSUBMISSION_FILE_FILEAREA, $submission->id);
    }

    /**
     * Return true if there are no submission files
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        return $this->count_files($submission->id, ASSIGNSUBMISSION_FORUM_FILEAREA) == 0;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_FORUM_FILEAREA=>$this->get_name());
    }

    /**
     * Check if there are forum contributions to be extracted for an assignment
     *
     * Cron function to be run periodically according to the moodle cron
     *
     * @return void
     */
    static function cron() {
        global $CFG, $DB;

        // for all assignments check if forum plugin is activated
        // if activated, check if the time set has now passed and that the extraction has not yet happened
        // get all assignments that fulfil these criteria
        // needs to call extract_submissions() for an assignment that needs to be cron triggered
        // EH: this query might be too time consuming - how to change it?
        // put the forum related assignment settings into a separate table? (as will have to be done once we allow selection of multiple forums)

        $sql = "SELECT a.id
                    FROM {assign_plugin_config} AS p
                    JOIN {assign} AS a ON p.assignment = a.id
                    JOIN {assign_plugin_config} AS p1 ON p.assignment = p1.assignment
                    JOIN {assign_plugin_config} AS p2 ON p.assignment = p2.assignment

                    WHERE p.plugin = 'forum'
                    AND p.subtype = 'assignsubmission'
                    AND p.name = 'enabled'
                    AND p.value = 1

                    AND p1.plugin = 'forum'
                    AND p1.subtype = 'assignsubmission'
                    AND p1.name = 'extractiondate'
                    AND p1.value < ?

                    AND p2.plugin = 'forum'
                    AND p2.subtype = 'assignsubmission'
                    AND p2.name = 'extracted'
                    AND p2.value = false";

        $assignment_ids = $DB->get_records_sql($sql, array(time()));

        if (empty($assignment_ids)) {
            mtrace('... no assignments awaiting extraction of forum contributions. ', '');
            return;
        }

        mtrace('... executing extraction of forum contrbutions in '.count($assignment_ids).' assignment(s) ... ', '');

        require_once($CFG->dirroot . '/mod/assign/submission/forum/locallib.php');

        foreach ($assignment_ids as $assignment_id) {
            // do the extraction - needs to be given assignment id
            assign_submission_forum::extract_submissions($assignment_id->id);
            // record that extraction has been done so it is not run again triggered by cron
            // cannot do this via set_config here, have to do it in db directly
            $conditions = array('assignment'=>$assignment_id->id, 'plugin'=>'forum', 'subtype'=>'assignsubmission', 'name'=>'extracted');
            $DB->set_field('assign_plugin_config', 'value', true, $conditions);
        }
    }

    /**
     * Extract submissions from discussions for an assignment
     *     This is called either from saving the assignment settings (for the assignment concerned) or
     *     via cron for an assignments selected by the cron function from the db
     *
     *     For each student in the course of the assignment
     *
     *         Create the html containing the discussion contributions of a user
     *         Create a submission record in the DB assign_submissions table (or udpate an already existing one)
     *         Create the actual html file in the file submission area (which also creates an entry in the files table)
     *
     * @param assignment $assignment
     * @return void
     */

    static function extract_submissions($assignment_id) {
        global $CFG, $COURSE, $DB, $USER;

        // get all students in course that can submit to this assignment
        // first, get the course id of the course the assignment belongs to
        $course_id = $DB->get_field('assign', 'course', array('id'=>$assignment_id), MUST_EXIST);
        // then the context
        $context = context_course::instance($course_id);
        // then all users who can submit to this assignment
        // Question: should this be linked to the forum from which we want to extract?
        // Later, could as well get teachers to extract their forum contributions and save them somewhere else
        $students = get_enrolled_users($context, "mod/assign:submit");
        // get context id of assignment (via the course module)
        $cm = get_coursemodule_from_instance('assign', $assignment_id, $course_id);
        $contextid_assignment = context_module::instance($cm->id)->id;
        // get the id from the forum from which to extract
        $conditions = array('assignment'=>$assignment_id, 'plugin'=>'forum', 'subtype'=>'assignsubmission', 'name'=>'forumidselected');
        $forum_id = $DB->get_field('assign_plugin_config', 'value', $conditions, MUST_EXIST);
        //mtrace('forum id: ' . $forum_id);
        // get the name of the forum
        $forum_name = $DB->get_field('forum', 'name', array('id'=>$forum_id), MUST_EXIST);

        foreach ($students as $student) {
            // extract discussion contributions
            $discussion_content = assign_submission_forum::get_user_forum_contribution($student, $forum_id, $forum_name);

            // need to record that something is submitted on the user's behalf
            $submission = assign_submission_forum::get_user_submission($student->id, $assignment_id);
            // create file in file submission area
            // the filename must be unique; as the extraction can happen multiple times, I have added date and time
            // going down to seconds didn't seem enough (as I sometimes got duplicate entries for key mdl_file_pat_uix)
            // I have added the last time to see if that fixes this issue (which seems to)
            $fs = get_file_storage();
            $fileinfo = array(
                'contextid' => $contextid_assignment,
                'component' => 'assignsubmission_forum',
                'filearea' => ASSIGNSUBMISSION_FILE_FILEAREA,
                'itemid' => $submission->id,
                'userid' => $student->id,
                'filepath' => '/',
                'filename' => 'ForumContributions_' . $student->lastname . '_' . date("Ymd", time()) . '_' . date("Hms", time()) . '_' . time() . '.html');
            $fs->create_file_from_string($fileinfo, $discussion_content);
        }
    }

    /**
     * Load the submission object for a particular user, optionally creating it if required
     * This method is similar to the one in assign/submission/file/locallib.php (it is private there)
     *
     * @param int $userid The id of the user whose submission we are working with
     * @param int $assignmentid The id of the assignment we want to see if the user already has a submission for
     * @return stdClass The submission
     */
    static private function get_user_submission($userid, $assignmentid) {
        global $DB, $USER;

        $submission = $DB->get_record('assign_submission', array('assignment'=>$assignmentid, 'userid'=>$userid));

        // for an existing submission record, should the timemodified be updated?
        if ($submission) {
            return $submission;
        }

        $submission = new stdClass();
        $submission->assignment   = $assignmentid;
        $submission->userid       = $userid;
        $submission->timecreated = time();
        $submission->timemodified = $submission->timecreated;

        $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

        $sid = $DB->insert_record('assign_submission', $submission);
        $submission->id = $sid;
        return $submission;
    }

    /**
     * Get the forum contribution for a particular user and forum
     * Format the contributions as xml and transform via xslt to html
     *
     * @param int $user The record of the user for whom we want to extract discussion contributions
     * @param int $forumid The id of the forum from which we want to extract
     * @return string The discussion contributions
     */
    static private function get_user_forum_contribution($user, $forumid, $forumname) {
        global $DB, $CFG;

        // prepare start of path for hrefs to forums, discussions and postings
        $href_start = $CFG->wwwroot . '/mod/forum/';
        //mtrace('path: ' . $href_start);
        // path to forum
        $path = $href_start . 'view.php?id=' . $forumid;

        // Create xml header
        $contribution = assign_submission_forum::header_forum_start($forumname, $user->firstname, $user->lastname);
        // get all forum discussion threads for the forum id sorted by discussions->id
        $discussions = $DB->get_records('forum_discussions', array('forum'=>$forumid), 'id');
        foreach ($discussions as $discussion) {
            // get all posts by the user for the discussion thread sorted by post id
            $posts = $DB->get_records('forum_posts', array('discussion'=>$discussion->id, 'userid'=>$user->id), 'id');
            // only include if user has made a contribution
            if (count($posts) > 0) {
                // path to discussion
                $path = $href_start . 'discuss.php?d=' . $discussion->id;
                // discussion opening tag
                $contribution .= assign_submission_forum::discussion_start(get_string('discussion', 'assignsubmission_forum') . ': ' . $discussion->name, $path);
                // add the posts
                foreach ($posts as $post) {
                    $date = userdate($post->modified);
                    // put the link to the post in context together
                    if ($post->parent == 0) {
                        $path_post = $path;
                    } else {
                        $path_post = $path . '#p' . $post->parent;
                    }
                    $text = get_string('seepostincontext', 'assignsubmission_forum');
                    // check if there has been an attachment to the post and prepare additional text accordingly
                    $add_text = '';
                    if ($post->attachment > 0) {
                        $add_text = get_string('posthasattachment', 'assignsubmission_forum');
                    }
                    $contribution .= assign_submission_forum::post_start($date, $text, $path_post, $add_text);
                    // format_text_email takes out the html tags (which is what we need, as otherwise the xml format is upset
                    $contribution .= assign_submission_forum::post_content(format_text_email($post->message, FORMAT_HTML));
                    //$contribution .= assign_submission_forum::post_content($post->message);

                    $contribution .= assign_submission_forum::closing_tag("post");
                }
                $contribution .= assign_submission_forum::closing_tag("discussion");
            }
        }
        $contribution .= assign_submission_forum::closing_tag("forum");
        mtrace($contribution);
        // convert xml into html
        $xml = new DOMDocument;
        $xml->loadxml($contribution);
        $xsl = new DOMDocument;
        $xsl->load($CFG->dirroot.'/mod/assign/submission/forum/stylesheet.xsl');
        // Configure the transformer
        $proc = new XSLTProcessor;
        // attach the xsl rules
        $proc->importStyleSheet($xsl);
        $contribution = $proc->transformToXML($xml);
        return $contribution;
    }

    /**
     * Create the header for the xml file and start forum tag
     *
     * @param string $forumname The name of the forum
     * @param string $firstname The first name of the user
     * @param string $lastname The last name of the user
     * @return string The header section of the xml file and the opening tag for the forum
     */
    static private function header_forum_start($forumname, $firstname, $lastname) {

        $xml = <<< EOD
<?xml version="1.0" encoding="UTF-8"?>
<forum title="$forumname" user="$firstname $lastname">

EOD;
        return $xml;
    }

    /**
     * Create the start for the discussion
     *
     * @param string $title The title of the discussion
     * @param string $laddress The path to the discussion in context
     * @return string The opening tag for the discussion
     */
    static private function discussion_start($title, $address) {

        $xml = <<< EOD
        <discussion title="$title" address="$address">

EOD;
        return $xml;
    }

    /**
     * Create the start for the post
     *
     * @param string $date The date the post was last modified
     * @param string $text The text to be shown for the link to the post
     * @param string $address The path to the posting in context
     * @return string The opening tag for the post
     */
    static private function post_start($date, $text, $address, $additionaltext) {

        $xml = <<< EOD
        <post date="$date" desc="$text" address="$address" add="$additionaltext">

EOD;
        return $xml;
    }

    /**
     * Create the content of the post
     *
     * @param string $content The content of the post
     * @return string The tags that contain the content of the post
     */
    static private function post_content($content) {

        // just in case $content is empty, which it shouldn't be
        if (!$content) {
            return "<text></text>";
        }
        // content can span multiple lines
        // put each line into its own set of <text> tags
        $lines = preg_split("/(\r\n|\n|\r)/", $content);
        $xml = "";
        foreach ($lines as $line) {
            $xml .= "<text>" . $line . "</text>";
        }
        return $xml;
    }

    /**
     * Create a closing tag
     * @param string $tagname The tag to return a closing for
     * @return string The closing tag
     */
    static private function closing_tag($tagname) {

        $xml = <<< EOD
        </$tagname>

EOD;
        return $xml;
    }
}
