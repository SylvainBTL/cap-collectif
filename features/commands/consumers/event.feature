@consumers
Feature: Event consumers

@rabbitmq @snapshot-email
Scenario: Email should be sent if a message is sent to the event_create queue
  Given I publish in "event_create" with message below:
  """
  {
    "eventId": "event5"
  }
  """
  And I consume "event_create"
  Then I open mail with subject 'event-needing-examination {"{eventTitle}":"ParisWeb2014"}'
  And email should match snapshot "notifyAdminOfNewEvent.html"
