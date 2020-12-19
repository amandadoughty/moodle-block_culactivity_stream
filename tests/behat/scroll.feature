@block @cul @block_culactivity_stream @block_culactivity_stream_scroll @javascript
Feature: Activity stream block scroll
  In order to be kept informed
  As a user
  I can scroll the Activity Feed block to view more notifications

  Background:
    # This will make sure CUL activity stream notifications are enabled and create
    # two assignment notifications. One for the student submitting their
    # assignment and another for the teacher grading it.
    Given the following "courses" exist:
        | fullname | shortname | category | groupmode |
        | Course 1 | C1 | 0 | 1 |
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
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I press "Blocks editing on"
    And I add the "CUL Activity Feed" block if not present
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
    And I log out
  
  Scenario: Scrolling loads more notifications
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Test assignment name 2 |
        | Description | Submit your online text |
        | assignsubmission_onlinetext_enabled | 1 |
        | assignsubmission_file_enabled | 0 |
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Test assignment name 3 |
        | Description | Submit your online text |
        | assignsubmission_onlinetext_enabled | 1 |
        | assignsubmission_file_enabled | 0 |
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Test assignment name 4 |
        | Description | Submit your online text |
        | assignsubmission_onlinetext_enabled | 1 |
        | assignsubmission_file_enabled | 0 |
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Test assignment name 5 |
        | Description | Submit your online text |
        | assignsubmission_onlinetext_enabled | 1 |
        | assignsubmission_file_enabled | 0 |
    And I add a "Assignment" to section "1" and I fill the form with:
        | Assignment name | Test assignment name 6 |
        | Description | Submit your online text |
        | assignsubmission_onlinetext_enabled | 1 |
        | assignsubmission_file_enabled | 0 |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment name 2"
    And I press "Add submission"
    And I set the following fields to these values:
        | Online text | I'm the student submission |
    And I press "Save changes"
    And I follow "Test assignment name 3"
    And I press "Add submission"
    And I set the following fields to these values:
        | Online text | I'm the student submission |
    And I press "Save changes"
    And I follow "Test assignment name 4"
    And I press "Add submission"
    And I set the following fields to these values:
        | Online text | I'm the student submission |
    And I press "Save changes"
    And I follow "Test assignment name 5"
    And I press "Add submission"
    And I set the following fields to these values:
        | Online text | I'm the student submission |
    And I press "Save changes"
    And I follow "Test assignment name 6"
    And I press "Add submission"
    And I set the following fields to these values:
      | Online text | I'm the student submission |
    And I press "Save changes"
    And the following "last access times" exist:
        | user | course | lastaccess |
        | student1 | C1 | ##yesterday## |    
    And I log out
    And I log in as "student1"
    # Confirm the feed is showing only 5 items.
    Then I should see "5" "new" items in feed
    # Confirm that scrolling adds remaining items
    And I scroll the activity feed
    Then I should see "6" "new" items in feed
    And I should see "No more notifications"
