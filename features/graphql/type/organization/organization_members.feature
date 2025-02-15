@organization
Feature: Organisation members

Scenario: GraphQL admin wants to get all members of an organization
  Given I am logged in to graphql as admin
  And I send a GraphQL request:
  """
  query {
    organization: node(id: "T3JnYW5pemF0aW9uOm9yZ2FuaXphdGlvbjE=") {
      ... on Organization {
        title
        members {
          totalCount
          edges {
            node {
              user {
                username
              }
              role
            }
          }
        }
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data":{
      "organization": {
        "title": "Communauté de commune de Parthenay",
        "members": {
          "totalCount": 4,
          "edges": [
            {
              "node": {
                "user": {
                  "username": "sfavot"
                },
                "role": "USER"
              }
            },
            {
              "node": {
                "user": {
                  "username": "lbrunet"
                },
                "role": "ADMIN"
              }
            },
            {
              "node": {
                "user": {
                  "username": "admin"
                },
                "role": "USER"
              }
            },
            {
              "node": {
                "user": {
                  "username": "user"
                },
                "role": "USER"
              }
            }
          ]
        }
      }
    }
  }
  """

Scenario: GraphQL user not organization admin wants to get all members of an organization
  Given I am logged in to graphql as theo
  And I send a GraphQL request:
  """
  query {
    organization: node(id: "T3JnYW5pemF0aW9uOm9yZ2FuaXphdGlvbjE=") {
      ... on Organization {
        title
        members {
          totalCount
          edges {
            node {
              user {
                username
              }
              role
            }
          }
        }
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data":{
      "organization": {
        "title": "Communauté de commune de Parthenay",
        "members": {
          "totalCount": 0,
          "edges": []
        }
      }
    }
  }
  """

Scenario: GraphQL anonymous wants to get all members of an organization
  And I send a GraphQL request:
  """
  query {
    organization: node(id: "T3JnYW5pemF0aW9uOm9yZ2FuaXphdGlvbjE=") {
      ... on Organization {
        title
        members {
          totalCount
          edges {
            node {
              user {
                username
              }
              role
            }
          }
        }
      }
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data":{
      "organization": {
        "title": "Communauté de commune de Parthenay",
        "members": {
          "totalCount": 0,
          "edges": []
        }
      }
    }
  }
  """