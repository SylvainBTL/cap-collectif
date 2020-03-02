@consultation @sources
Feature: Source

@database
Scenario: User wants to add a source in a contribuable opinion
  Given I am logged in as user
  And I go to an opinion with no sources
  When I go on the sources tab
  And I wait "#source-form__add" to appear on current page
  Then I wait "opinion.no_new_source" to appear on current page in "#opinion-page-tabs-pane-sources"
  When I create a new source
  Then I should see "alert.success.add.source" in the "#global-alert-box" element
  And I should see my new source

Scenario: Can not create a source in non-contribuable project
  Given I am logged in as user
  And I go to an opinion in a closed step
  Then I should see "step.consultation.alert.ended.title" in the "#main" element
  And I go on the sources tab
  Then the create source button should be disabled

@database
Scenario: Can vote for a source
  Given I am logged in as admin
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  When I vote for the first source
  When I delete my vote for the first source

# Update
@database
Scenario: Author of a source loose their votes when updating it
  Given I am logged in as user
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  When I edit my source
  Then I should see "alert.success.update.source" in the "#global-alert-box" element
  And my source should have lost its votes

@database
Scenario: Author of a source try to update without checking the confirm checkbox
  Given I am logged in as user
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  When I edit my source without confirming my votes lost
  Then I should see "source.constraints.check"

Scenario: Non author of a source can not update or delete
  Given I am logged in as admin
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  Then I should not see the source edit button
  Then I should not see the source delete button

# Delete
@database
Scenario: Author of a source wants to delete it
  Given I am logged in as user
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  When I delete my source
  And I wait 2 seconds
  Then I should see "alert.success.delete.source" in the "#global-alert-box" element
  And I should not see my source anymore

# Reporting
@security
Scenario: Author of a source can not report it
  Given feature "reporting" is enabled
  And I am logged in as user
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  And I should not see the source report button

@database
Scenario: Non author of a source can report it
  Given feature "reporting" is enabled
  And I am logged in as admin
  And I go to an opinion
  And I go on the sources tab
  And I wait "#sources-list" to appear on current page
  And I click the source report button
  And I fill the reporting form
  And I submit the reporting form
  Then I should see "alert.success.report.source" in the "#global-alert-box" element
