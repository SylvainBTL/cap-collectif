@admin @moderation @proposal_page_admin
Feature: Edit a proposal

#TODO do not have time to correctly fix this
@database @elasticsearch
Scenario: Logged in admin wants edit a proposal content
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  And I fill the proposal content address with "5 Allée Rallier du Baty, 35000 Rennes, France"
  And I fill in the following:
    | title | Proposition pas encore votable |
    | summary | "Un super résumé" |
    | proposal_body | "Look, just because I don't be givin' no man a foot massage don't make it right for Marsellus to throw Antwone into a glass motherfucking' house, fucking' up the way the nigger talks. Motherfucker do that shit to me, he better paralyze my ass, 'cause I'll kill the motherfucker, know what I'm sayin'?" |
    | responses[2].value  | HAHAHA |
  And I change the proposals "category" with option "Politique"
  And I attach the file "/var/www/features/files/image.jpg" to "proposal_media_field"
  And I attach the file "/var/www/features/files/document.pdf" to "proposal-admin-edit-responses3_field"
  And I wait 3 seconds
  Then I save current admin content proposal
  And I wait 2 seconds
  And I wait ".alert__form_succeeded-message" to appear on current page

@database @elasticsearch
Scenario: Logged in admin wants edit a proposal advancement tab
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal advancement tab
  And I check element "selections[0].selected"
  And I wait "#proposal-admin-page-tabs-pane-3" to appear on current page
  And I change the proposal advancement select "proposal advancement selection status" with option "Soumis au vote"
  Then I save current proposal admin advancement
  And I wait ".alert__form_succeeded-message" to appear on current page

@database @elasticsearch
Scenario: Logged in admin, wants to change the proposal's status
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal status tab
  And I click on DRAFT status
  And I save the proposal's status
  And I wait ".alert__form_succeeded-message" to appear on current page

@database @elasticsearch
Scenario: Logged in admin, wants to delete a proposal and check if followers are not present
  Given I am logged in as super admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal status tab
  And I delete the proposal
  And I confirm the admin proposal deletion
  And I should see status DELETED
  Then I reload the page
  And I go to the admin proposal followers tab
  And I should not see an ".proposal__follower" element

@database @elasticsearch
Scenario: Logged in admin, wants to delete a proposal and re published it
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal status tab
  And I delete the proposal
  And I confirm the admin proposal deletion
  And I wait ".btn-group.disabled" to appear on current page
  And I should see status DELETED

@database @elasticsearch
Scenario: Logged in admin, wants to view the proposal's followers
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal followers tab
  And I should see 2 ".proposal__follower" elements
  

@database @elasticsearch
Scenario: Logged in admin wants add a proposal realisation step
  Given I am logged in as admin
  And I go to the admin proposal page with proposalid "proposal10"
  Then I go to the admin proposal advancement tab
  And I check element "selections[4].selected"
  And I wait "#proposal-admin-page-tabs-pane-2" to appear on current page
  And I click on button "[id='proposal-admin-progress-steps-add']"
  And I wait "#realisation-step-modal" to appear on current page
  And I fill in the following:
    | progressSteps[0].title    | Banque      |
  And I click on button "#ProposalAdminRealisationStepModal-submit"
  And I wait "#realisation-step-modal" to disappear on current page
  And I click on button "#proposal_advancement_save"
  And I wait ".alert__form_succeeded-message" to appear on current page
