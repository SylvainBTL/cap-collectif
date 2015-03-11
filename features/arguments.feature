Feature: Arguments

  Scenario: Can create an argument in contribuable opinion
    Given I am logged in as user
    And I visited opinionPage with:
      | consultation_slug | croissance-innovation-disruption |
      | opinion_type_slug | causes                           |
      | opinion_slug      | ducimus-qui                      |
    When I submit a "yes" argument with text "Texte de mon argument"
    Then I should see "Merci ! Votre argument a bien été enregistré."

  Scenario: Can not create an argument in non-contribuable opinion
    Given I am logged in as user
    And I visited opinionPage with:
      | consultation_slug | strategie-technologique-de-l-etat-et-services-publics |
      | opinion_type_slug | causes                                                |
      | opinion_slug      | sint-in-molestias                                     |
    Then I should not see "Argument yes field" from "opinionPage"
    And I should not see "Argument no field" from "opinionPage"
