@bp @proposal_evaluations @deprecated
Feature: Proposals Evaluations

Scenario: Logged in evaluer wants to see his evaluations
  Given I am logged in as user
  And I go to the evaluations page
  Then there should be 3 evaluations
