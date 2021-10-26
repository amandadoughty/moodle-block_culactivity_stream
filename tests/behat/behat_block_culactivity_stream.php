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
 * Steps definitions for CUL Activity Stream.
 *
 * @package   block_culactivity_stream
 * @category  test
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../lib/tests/behat/behat_general.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * CUL Activity Stream block definitions.
 *
 * @package   block_culactivity_stream
 * @category  test
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_culactivity_stream extends behat_base {

    /**
     * Adds the CUL Activity block. Editing mode must be previously enabled.
     *
     * @Given /^I add the CUL Activity Feed block$/
     */
    public function i_add_the_cul_activity_feed_block() {
        try {
            // Try with pluginname (Boost flat navigation).
            $this->execute('behat_blocks::i_add_the_block', ['CUL Activity Feed']);
        } catch (Exception $e) {
            // Try with block title (Classic Add a block).
            try {
                $this->execute('behat_blocks::i_add_the_block', ['Activity feed']);
            } catch (Exception $e) {
                $this->execute('behat_blocks::i_add_the_block', ['Module updates']);
            }
        }
    }    

    /**
     * Checks the number of items in the feed.
     *
     * @Then /^I should see "(?P<number_string>[^"]*)" "(?P<status_string>[^"]*)" items in feed$/
     *
     * @param string $number
     * @param string $status
     */
    public function i_should_see_items_in_feed($number, $status) {
        try {
            $nodes = $this->find_all('css', ".block_culactivity_stream ul.notifications li.{$status}");
            $actualnumber = count($nodes);
        } catch (Behat\Mink\Exception\ElementNotFoundException $e) {
            $actualnumber = 0;
        }

        if ($actualnumber != (int)$number) {
            throw new ExpectationException(
                "Expected '{$number}' items but found '{$actualnumber}'.",
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * Scrolls down the activity feed.
     *
     * @When /^I scroll the activity feed$/
     *
     */
    public function i_scroll_the_activity_feed() {
        // Exception if it timesout and the element is still there.
        $msg = "No items were lazy loaded";
        $exception = new ExpectationException($msg, $this->getSession());
        $selector = '.block.block_culactivity_stream .culactivity_stream';
        $script = <<<EOF
        (function() {
            $('{$selector}').scrollTop($('{$selector}')[0].scrollHeight)
        })()
EOF;
        // It will stop spinning once the feed lazy loads a 6th item.
        $this->spin(
            function() use ($script) {
                $this->getSession()->evaluateScript($script);
                $nodes = $this->find_all('css', ".block_culactivity_stream ul.notifications li");
                $actualnumber = count($nodes);

                if ($actualnumber > 5) {
                    return true;
                }
                return false;
            },
            [],
            self::get_extended_timeout(),
            $exception,
            true
        );
    }

    /**
     * Creates a fake notification.
     *
     * @When /^new notification "(?P<notification_string>[^"]*)" is created$/
     *
     */
    public function new_notification_is_created ($notification) {
        $user = $this->get_session_user();
        $eventdata = new \core\message\message();
        $eventdata->courseid = 1;
        $eventdata->name = 'fake_notification';
        $eventdata->component = 'block_culactivity_stream';
        $eventdata->userfrom = $user;
        $eventdata->subject = $notification;
        $eventdata->fullmessage = $notification;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = $notification;
        $eventdata->smallmessage = $notification;
        $eventdata->notification = 1;
        $eventdata->userto = $user;
        $messageid = message_send($eventdata);

        if (!$messageid) {
            throw new Exception(get_string('messageerror', 'block_culactivity_stream'));
        }
    }
}