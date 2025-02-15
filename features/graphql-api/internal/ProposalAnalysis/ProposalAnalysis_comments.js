const ProposalAnalysisComments = /** GraphQL */ `
  query ProposalAnalysisComments($id: ID!) {
    node(id: $id) {
      ... on Proposal {
        analyses {
          id
          state
          analyst {
            username
          }
          comments {
            edges {
              node {
                id
                body
                author {
                  username
                }
              }
            }
          }
        }
      }
    }
  }
`;

describe('ProposalAnalysis.comments', () => {
  it("should fetch analysis comments", async () => {
    await expect(
      graphql(
        ProposalAnalysisComments,
        {
          id: 'UHJvcG9zYWw6cHJvcG9zYWwxMDk=',
        },
        'internal_supervisor',
      ),
    ).resolves.toMatchSnapshot();
  });
});
