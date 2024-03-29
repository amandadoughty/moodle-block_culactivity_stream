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
 * CUL Activity Stream renderer
 *
 * @package    block
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

require_once('renderers/course_picture.php');

/**
 * Main class for rendering the culactivity_stream block
 */
class block_culactivity_stream_renderer extends plugin_renderer_base {

    public $instanceid = null;

    /**
     * Function for rendering the notification wrapper
     *
     * @global stdClass $USER
     * @param array $notifications array of notification objects
     * @param int $page page that user is on if no JS
     * @return string $output html
     */
    public function culactivity_stream($notifications, $page, $instanceid) {
        global $USER;

        $this->instanceid = $instanceid;
        $returnurl = $this->page->url->out();
        $output = '';
        $id = html_writer::random_id('culactivity_stream');
        // Start content generation.
        $output .= html_writer::start_tag('div', [
            'id' => $id,
            'class' => 'culactivity_stream']);
        $output .= html_writer::start_tag('ul', ['class' => 'notifications']);
        $output .= $this->culactivity_stream_items ($notifications, $returnurl, $instanceid, $page);
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Function for appending reload button and ajax loading gif to title
     *
     * @return string $output html
     */
    public function culactivity_stream_reload () {
        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'reload'));
        // Reload button.
        $reloadimg = $this->output->pix_icon('i/reload', '', 'moodle',
                array('class' => 'smallicon'));
        $reloadsr = html_writer::tag('span', get_string('reload', 'block_culactivity_stream'), ['class' => 'accesshide']);
        $reloadurl = $this->page->url;
        $reloadattr = [
            'id' => 'block_culactivity_stream_reload',
            'class' => 'block_culactivity_stream_reload'
        ];
        $output .= html_writer::link($reloadurl, $reloadimg . $reloadsr, $reloadattr);
        // Loading gif.
        $ajaximg = $this->output->pix_icon('i/loading_small', '');
        $output .= html_writer::tag('span', $ajaximg, array('class' => 'block_culactivity_stream_loading'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Function to render the individual notification list items
     *
     * @param array $notifications array of notification objects
     * @param int $page page that user is on if no JS
     * @return string $output html
     */
    public function culactivity_stream_items ($notifications, $returnurl, $instanceid, $page = 1) {
        $output = '';
        $times = array();

        foreach ($notifications as $notification) {
            $class = $notification->new ? 'new' : 'old';
            $output .= html_writer::start_tag('li',
                array(
                    'id' => 'm_'.$notification->id,
                    'class' => $class)
                );
            $output .= html_writer::start_tag('div', array('class' => 'clearfix notifictionitem'));

            // Avatar.
            $output .= html_writer::start_tag('div', array('class' => 'avatar'));
            $output .= empty($notification->img) ? '' : $notification->img;
            $output .= html_writer::end_tag('div'); // Closing div: .avatar.

            // Text.
            $output .= html_writer::start_tag('div', array('class' => 'notificationtext'));
            $output .= html_writer::start_tag('span');
            $output .= strip_tags($notification->smallmessage);
            $output .= html_writer::end_tag('span');
            $output .= html_writer::end_tag('div'); // Closing div: .notificationtext.

            // Activity icon.
            $output .= html_writer::start_tag('div', array('class' => 'activityicon'));
            $plugin = explode('_', $notification->component);

            if (reset($plugin) == 'mod') {
                $icon = 'monologo';
            } else {
                $icon = $notification->icon;
            }

            // TODO move this logic?
            if ($this->page->theme->resolve_image_location(
                    $icon,
                    $notification->component,
                    true)
                ) {
                $output .= $this->output->pix_icon(
                    $icon,
                    $notification->title,
                    $notification->component,
                    array('class' => 'icon')
                );
            } else {
                $output .= $this->output->pix_icon(
                    'spacer',
                    '',
                    'moodle',
                    array('class' => 'icon noicon')
                );
            }

            $output .= html_writer::end_tag('div'); // Closing div: .activityicon.
            $output .= html_writer::end_tag('div'); // Closing div: .notifictionitem.

            // Meta data.
            $output .= html_writer::start_tag('div', array('class' => 'meta'));

            // Time since.
            $output .= html_writer::start_tag('div', array('class' => 'timesince'));
            $output .= html_writer::start_tag('span');
            $output .= get_string('time', 'block_culactivity_stream', $notification->time);
            $output .= html_writer::end_tag('span');
            $output .= html_writer::end_tag('div'); // Closing div: .timesince.

            // Visit and Remove links.
            $output .= html_writer::start_tag('div', array('class' => 'contexturls'));

            if (isset($notification->contexturl) && $notification->contexturl) {
                $output .= html_writer::link($notification->contexturl, $notification->contexturlname);
                $output .= ' | ';
            }

            $removeurl = new moodle_url('/blocks/culactivity_stream/remove_post.php',
                    ['remove' => $notification->id,
                        'block_culactivity_stream_page' => $page,
                        'sesskey' => sesskey(),
                        'returnurl' => $returnurl,
                        'anchor' => 'inst' . $instanceid
                    ]
                );
            $output .= html_writer::link($removeurl, get_string('remove'),
                    array('class' => 'removelink'));
            $output .= html_writer::end_tag('div'); // Closing div: .contexturls.
            $output .= html_writer::end_tag('div'); // Closing div: .meta.
            $output .= html_writer::end_tag('li');
            $output .= '<hr/>';
        }

        return $output;
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param int $prev the previous page number
     * @param int $next the next page number
     * @return string $output html
     */
    public function culactivity_stream_pagination($prev=false, $next=false) {
        $baseurl = $this->page->url;
        $anchor = 'inst' . $this->instanceid;
        $output = '';

        if ($prev || $next) {
            $output .= html_writer::start_tag('div', ['class' => 'pages']);

            if ($prev) {
                $prevurl = new moodle_url($baseurl, ['block_culactivity_stream_page' => $prev], $anchor);
                $prevtext = get_string('newer', 'block_culactivity_stream');
                $output .= html_writer::link($prevurl, $prevtext);
            }

            if ($prev && $next) {
                $output .= '&nbsp;|&nbsp;';
            }

            if ($next) {
                $nexturl = new moodle_url($baseurl, ['block_culactivity_stream_page' => $next], $anchor);
                $nexttext = get_string('older', 'block_culactivity_stream');
                $output .= html_writer::link($nexturl, $nexttext);
            }

            $output .= html_writer::end_tag('div'); // Closing div: .pages.
        }

        return $output;
    }
}
