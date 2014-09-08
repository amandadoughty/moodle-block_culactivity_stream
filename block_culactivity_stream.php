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
 * CUL Activity Stream Block
 *
 * @package    block
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once($CFG->dirroot.'/blocks/culactivity_stream/locallib.php');


/**
 * Main culactivity_stream block class for display on course pages
 */
class block_culactivity_stream extends block_base {

    /*
     * The htmltitle of the block to be displayed in the block title area.
     * @public string $htmltitle
     */
    public $htmltitle = null;

    /**
     * Initialiser
     */
    public function init() {
        $this->title = get_string('activitystream', 'block_culactivity_stream');
    }

    /**
     * Fetches content for the block when displayed
     * @return string Block content
     */
    public function get_content() {
        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $limitnum = 5;
        $page = optional_param('block_culactivity_stream_page', 1, PARAM_RAW);
        $limitfrom = $page > 1 ? ($page * $limitnum) - $limitnum : 0;

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        list($count, $notifications) = block_culactivity_stream_get_notifications($COURSE->id, 0, $limitfrom, $limitnum);
        $renderer = $this->page->get_renderer('block_culactivity_stream');
        // Can't do this in init.
        $this->htmltitle = $renderer->culactivity_stream_reload();
        $this->content->text = $renderer->culactivity_stream($notifications, $page);

        $prev = false;
        $next = false;

        if ($page > 1) {
            // Add a 'newer' link.
            $prev = $page - 1;
        }

        if (($limitfrom + $limitnum) < $count) {
            // Add an 'older' link.
            $next = $page + 1;
        }

        $this->content->text .= $renderer->culactivity_stream_pagination($prev, $next);

        $this->page->requires->yui_module('moodle-block_culactivity_stream-scroll',
                'M.block_culactivity_stream.scroll.init',
                array(array('limitnum' => $limitnum, 'count' => $count, 'courseid' => $COURSE->id)));

        return $this->content;
    }

    /**
     * Returns a list of formats, and whether the block
     * should be displayed within them. culactivity_stream should
     * only be displayed within courses.
     * @return array(string => boolean) List of formats
     */
    public function applicable_formats() {
        return array('site' => true, 'my-index' => true, 'course' => true);
    }

    /**
     * Returns whether multiple culactivity_stream blocks
     * are allowed within the same course.
     * In the case of culactivity_stream, no.
     * @return boolean Multiple blocks?
     * @version 2010081801
     * @since 2010081801
     */
    public function instance_allow_multiple() {
        return false;
    }

    public function hide_header() {
        return false;
    }

    public function has_config() {
        return true;
    }

    /**
     * Return a block_contents object representing the full contents of this block.
     *
     * This internally calls ->get_content(), and then adds the editing controls etc.
     *
     * You probably should not override this method, but instead override
     * {@link html_attributes()}, {@link formatted_contents()} or {@link get_content()},
     * {@link hide_header()}, {@link (get_edit_controls)}, etc.
     *
     * @return block_contents a representation of the block, for rendering.
     * @since Moodle 2.0.
     */
    public function get_content_for_output($output) {

        $bc = parent::get_content_for_output($output);

        if (!$this->hide_header()) {
            $bc->title = $this->htmltitle;
        }

        return $bc;
    }
}

