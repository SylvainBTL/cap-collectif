@project
Feature: Project contributors

@elasticsearch
Scenario: GraphQL client want to get the list of contributors
  Given I send a GraphQL POST request:
  """
  {
    "query": "query node ($project: ID!){
      project: node(id: $project) {
        ... on Project {
          contributors(first: 5) {
            totalCount
            anonymousCount
            pageInfo {
              hasNextPage
              endCursor
            }
            edges {
              node {
                id
              }
            }
          }
        }
      }
    }",
    "variables": {
      "project": "UHJvamVjdDpwcm9qZWN0MQ=="
    }
  }
  """
  Then the JSON response should match:
  """
  {
    "data": {
      "project": {
        "contributors": {
          "totalCount": @integer@,
          "anonymousCount": 0,
          "pageInfo": {
            "hasNextPage": true,
            "endCursor": "YToyOntpOjA7aTotOTIyMzM3MjAzNjg1NDc3NTgwODtpOjE7aToxNDgzMjI3MzYwMDAwO30="
          },
          "edges": [
            {"node":{"id": @string@ }},
            {"node":{"id": @string@ }},
            {"node":{"id": @string@ }},
            {"node":{"id": @string@ }},
            {"node":{"id": @string@ }}
          ]
        }
      }
    }
  }
  """
