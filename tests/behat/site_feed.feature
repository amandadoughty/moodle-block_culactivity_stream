@block @cul @block_culactivity_stream @block_culactivity_stream_site_feed @javascript
Feature: Activity stream block used on the frontpage
  In order to be kept informed
  As a user
  I see a feed of all notifications on the front page

  Background:
    # This will make sure CUL activity stream notifications are enabled and create
    # two assignment notifications. One for the student submitting their
    # assignment and another for the teacher grading it.
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
      | Course 2 | C2 | 0 | 1 |
    # Make sure the activity stream notifications are enabled for assignments.
    And the following config values are set as admin:
      | culactivity_stream_provider_mod_assign_assign_notification_permitted | permitted | message |
      | message_provider_mod_assign_assign_notification_loggedin | culactivity_stream | message |
      | message_provider_mod_assign_assign_notification_loggedoff | culactivity_stream | message |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | teacher1 | C2 | editingteacher |
      | student1 | C2 | student |
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I press "Blocks editing on"
    And I add the "Activity Feed" block if not present
    And I press "Reset Dashboard for all users"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name | Test assignment name 1 |
      | Description | Submit your online text |
      | assignsubmission_onlinetext_enabled | 1 |
      | assignsubmission_file_enabled | 0 |
    And I am on "Course 2" course homepage
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name | Test assignment name 2 |
      | Description | Submit your online text |
      | assignsubmission_onlinetext_enabled | 1 |
      | assignsubmission_file_enabled | 0 |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name"
    And I press "Add submission"
    # This should generate a notification.
    And I set the following fields to these values:
      | Online text | I'm the student first submission |
    And I press "Save changes"
    And I am on "Course 2" course homepage
    And I follow "Test assignment name 2"
    And I press "Add submission"
    # This should generate a notification.
    And I set the following fields to these values:
      | Online text | I'm the student first submission |
    And I press "Save changes"
    And the following "last access times" exist:
      | user | course | lastaccess |
      | student1 | C1 | ##yesterday## |    
    And I log out

  Scenario: Activity stream shows new notifications
    When I log in as "student1"
    # Confirm the feed is showing two new notifications.
    Then I should see "2" "new" items in feed
    And I should see "0" "old" items in feed
    # Confirm the submission notification is visible.
    And I should see "You have submitted your assignment submission for Test assignment name 1" in the "block_culactivity_stream" "block"
    And I should see "You have submitted your assignment submission for Test assignment name 2" in the "block_culactivity_stream" "block"
    
  Scenario: Clicking view opens relevant page
    Given I log in as "student1"
    And I click on "Visit" "link" in the "You have submitted your assignment submission for Test assignment name 1" "list_item"
    And "title[text() = 'C1: Test assignment name 1']" "xpath_element" should exist in the "head" "css_element"
    And the following "last access times" exist:
      | user | course | lastaccess |
      | student1 | C1 | ##tomorrow## |    
    And I log out
    And I log in as "student1"
    # Confirm the feed is showing one visited notification.
    And I should see "1" "old" items in feed
    And I should see "1" "new" items in feed

  Scenario: Clicking remove deletes the item from the feed
    Given I log in as "student1"
    And I click on "Remove" "link" in the "You have submitted your assignment submission for Test assignment name 1" "list_item"
    # Confirm the feed is showing no notifications.
    And I should see "0" "old" items in feed
    And I should see "1" "new" items in feed