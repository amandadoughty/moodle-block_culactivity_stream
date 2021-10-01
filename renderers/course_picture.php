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
 * Classes representing HTML elements, used by $OUTPUT methods
 *
 * Please see http://docs.moodle.org/en/Developement:How_Moodle_outputs_HTML
 * for an overview.
 *
 * @package    block
 * @subpackage culactivity_stream
 * @copyright 2014 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_culactivity_stream_renderers_course_picture_renderer extends plugin_renderer_base {

    /**
     * Returns HTML to display the specified course's avatar.
     *
     * Course avatar may be obtained in two ways:
     * <pre>
     * // Option 1: (shortcut for simple cases, preferred way)
     * // $course has come from the DB and has fields id
     * $OUTPUT->course_picture($course, array('popup'=>true));
     *
     * // Option 2:
     * $coursepic = new course($course);
     * // Set properties of $coursepic
     * $coursepic->popup = true;
     * $OUTPUT->render($coursepic);
     * </pre>
     *
     * @param stdClass $course Object with at least fields id, picture, imagealt, firstname, lastname
     *     If any of these are missing, the database is queried. Avoid this
     *     if at all possible, particularly for reports. It is very bad for performance.
     * @param array $options associative array with course picture options, used only if not a course_picture object,
     *     options are:
     *     - size=35 (size of image)
     *     - link=true (make image clickable - the link leads to course)
     *     - popup=false (open in popup)
     *     - alttext=true (add image alt attribute)
     *     - class = image class attribute (default 'coursepicture')
     * @return string HTML fragment
     */
    public function course_picture(stdClass $course, array $options = null) {
        $coursepicture = new course_picture($course);
        foreach ((array)$options as $key => $value) {
            if (array_key_exists($key, $coursepicture)) {
                $coursepicture->$key = $value;
            }
        }
        return $this->render($coursepicture);
    }

    /**
     * Internal implementation of course image rendering.
     *
     * @param course_picture $coursepicture
     * @return string
     */
    protected function render_course_picture(course_picture $coursepicture) {
        global $CFG, $DB;

        $course = $coursepicture->course;
        $coursedisplayname = $course->shortname;

        if ($coursepicture->alttext) {
            $alt = get_string('pictureof', '', $coursedisplayname);
        } else {
            $alt = '';
        }

        $class = $coursepicture->class;
        $src = $coursepicture->get_url($this->page, $this);
        $attributes = array('src' => $src, 'alt' => $alt, 'title' => $alt, 'class' => $class);

        // Get the image html output first.
        $output = html_writer::empty_tag('img', $attributes);;

        // Then wrap it in link if needed.
        if (!$coursepicture->link) {
            return $output;
        }

        $url = new moodle_url('/course/view.php', array('id' => $course->id));
        $attributes = array('href' => $url);

        if ($coursepicture->popup) {
            $id = html_writer::random_id('coursepicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);
        }

        return html_writer::tag('a', $output, $attributes);
    }
};

/**
 * Data structure representing a course picture.
 *
 * @copyright 2014 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Modle 2.6
 * @package local
 * @category culoutput
 */
class course_picture implements renderable {
    /**
     * @var array List of mandatory fields in user record here. (do not include
     * TEXT columns because it would break SELECT DISTINCT in MSSQL and ORACLE)
     */
    protected static $fields = array('id', 'shortname', 'idnumber');

    /**
     * @var stdClass A course object with at least fields all columns specified
     * in $fields array constant set.
     */
    public $course;

    /**
     * @var bool Add course link to image
     */
    public $link = true;

    /**
     * @var bool Add non-blank alt-text to the image.
     * Default true, set to false when image alt just duplicates text in screenreaders.
     */
    public $alttext = true;

    /**
     * @var bool Whether or not to open the link in a popup window.
     */
    public $popup = false;

    /**
     * @var string Image class attribute
     */
    public $class = 'coursepicture';

    /**
     * Course picture constructor.
     *
     * @param stdClass $course course record with at least id, picture, imagealt, coursename set.
     *                 It is recommended to add also contextid of the course for performance reasons.
     */
    public function __construct(stdClass $course) {
        global $CFG, $DB;

        if (empty($course->id)) {
            throw new coding_exception('Course id is required when printing course avatar image.');
        }

        // Only touch the DB if we are missing data and complain loudly.
        $needrec = false;

        foreach (self::$fields as $field) {
            if (!property_exists($course, $field)) {
                $needrec = true;
                debugging('Missing '.$field
                    .' property in $course object, this is a performance problem that needs to be fixed by a developer. '
                    .'Please use course_picture::fields() to get the full list of required fields.', DEBUG_DEVELOPER);
                break;
            }
        }

        if ($needrec) {
            $this->course = $DB->get_record('course', array('id' => $course->id), self::fields(), MUST_EXIST);
        } else {
            $this->course = new core_course_list_element($course);
        }
    }

    /**
     * Works out the URL for the course picture.
     *
     * This method is recommended as it avoids costly redirects of course pictures
     * if requests are made for non-existent files etc.
     *
     * @param moodle_page $page
     * @param renderer_base $renderer
     * @return moodle_url
     */
    public function get_url(moodle_page $page, renderer_base $renderer = null) {
        global $CFG, $DB, $OUTPUT;

        if (is_null($renderer)) {
            $renderer = $page->get_renderer('core');
        }

        $defaulturl = $renderer->image_url('u/f2'); // Default image.

        if ((!empty($CFG->forcelogin) and !isloggedin()) ||
            (!empty($CFG->forceloginforprofileimage) && (!isloggedin() || isguestuser()))) {
            // Protect images if login required and not logged in
            // also if login is required for profile images and is not logged in or guest
            // do not use require_login() because it is expensive and not suitable here anyway.
            return $defaulturl;
        }

        if ($this->course->has_course_overviewfiles()) {
            foreach ($this->course->get_course_overviewfiles() as $file) {
                try {
                    $isimage = $file->is_valid_image();
                } catch (exception $e) {
                    $isimage = false;
                }

                if ($isimage) {
                    $url = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        null,
                        $file->get_filepath(),
                        $file->get_filename()
                    );
                    return $url;
                }
            }

        } else {
            return $OUTPUT->get_generated_image_for_id($this->course->id);
        }

        return $defaulturl;
    }
}
