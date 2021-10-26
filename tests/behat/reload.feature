@block @cul @block_culactivity_stream @block_culactivity_stream_reload @javascript
Feature: CUL Activity Feedblock automatic reload
  In order to be kept informed
  As a user
  I see new notifications in the Activity Feed block

  Background:
    # This will make sure CUL Activity Feed block  notifications are enabled and create
    # two assignment notifications. One for the student submitting their
    # assignment and another for the teacher grading it.
    Given the following "courses" exist:
        | fullname | shortname | category | groupmode |
        | Course 1 | C1 | 0 | 1 |
    # Make sure the CUL Activity Feed block notifications are enabled for assignments.
    And the following config values are set as admin:
        | culactivity_stream_provider_mod_assign_assign_notification_permitted | permitted | message |
        | culactivity_stream_provider_block_culactivity_stream_fake_notification_permitted | permitted | message |
        | message_provider_mod_assign_assign_notification_loggedin | culactivity_stream | message |
        | message_provider_mod_assign_assign_notification_loggedoff | culactivity_stream | message |
        | message_provider_block_culactivity_stream_fake_notification_loggedin | culactivity_stream | message |
        | message_provider_block_culactivity_stream_fake_notification_loggedoff | culactivity_stream | message |
    And the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@example.com |
        | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | student1 | C1 | student |
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I press "Blocks editing on"
    And I add the CUL Activity Feed block
    And I press "Reset Dashboard for all users"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Test assignment name |
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
    And the following "last access times" exist:
        | user | course | lastaccess |
        | student1 | C1 | ##yesterday## |
    And I log out

  Scenario: Feed refreshes when reloaded
    Given I log in as "student1"
    And I am on homepage
    Then I should see "1" "new" items in feed
    And new notification "I am a fake notification" is created
    And I click on "Refresh Feed" "link"
    Then I should see "2" "new" items in feed
    And "I am a fake notification" "list_item" should appear before "Test assignment name" "list_item"

  Scenario: Feed refreshes every 5 mins
    Given I log in as "student1"
    And I am on homepage
    Then I should see "1" "new" items in feed
    And new notification "I am a fake notification" is created
    And I wait "310" seconds
    Then I should see "2" "new" items in feed
    And "I am a fake notification" "list_item" should appear before "Test assignment name" "list_item"
