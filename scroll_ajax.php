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
 * Infinite scrolling functionality for culactivity_stream block.
 *
 * @package    block
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_sesskey();
require_login();

$PAGE->set_context(context_system::instance());

$limitnum = required_param('limitnum', PARAM_INT);
$lastid = required_param('lastid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$returnurl = required_param('returnurl', PARAM_RAW);
$instanceid = required_param('instanceid', PARAM_INT);
$list = '';
$end = false;

// Get more notifications.
list($count, $notifications) = block_culactivity_stream_get_notifications($courseid, $lastid, 0, $limitnum, false);
$renderer = $PAGE->get_renderer('block_culactivity_stream');

if ($notifications) {
    $list .= $renderer->culactivity_stream_items ($notifications, $returnurl, $instanceid);
} else {
    $list .= html_writer::tag('li', get_string('nomorenotifications', 'block_culactivity_stream'));
    $end = true;
}

echo json_encode(array('output' => $list, 'end' => $end));
