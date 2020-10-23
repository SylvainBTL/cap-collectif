// @flow
import React from 'react';
import { createFragmentContainer, graphql } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { Nav, NavItem } from 'react-bootstrap';
import styled, { type StyledComponent } from 'styled-components';
import colors from '~/utils/colors';
import { isInterpellationContextFromProposal } from '~/utils/interpellationLabelHelper';
import type { ProposalPageTabs_proposal } from '~relay/ProposalPageTabs_proposal.graphql';
import type { ProposalPageTabs_step } from '~relay/ProposalPageTabs_step.graphql';

type Props = {
  proposal: ?ProposalPageTabs_proposal,
  step: ?ProposalPageTabs_step,
  tabKey: string,
  votesCount: number,
};

const Tabs: StyledComponent<{ loading: boolean }, {}, HTMLDivElement> = styled.div`
  height: 84px;
  width: 100%;
  background: ${colors.white};
  box-shadow: 0 6px 12px 0 rgba(0, 0, 0, 0.12);
  margin-bottom: 30px;
  overflow: scroll;

  /* Hide scrollbar for Chrome, Safari and Opera */
  :-webkit-scrollbar {
    display: none;
  }

  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */

  ul {
    margin: auto;
    width: 100%;
    max-width: 950px;
    display: flex;
    align-items: center;
    height: 100%;
  }

  li {
    height: 100%;
  }

  a {
    display: flex !important;
    outline: none;
    height: 100%;
    padding-top: 28px !important;
    color: ${colors.black} !important;

    span {
      opacity: ${({ loading }) => loading && '0.5'};
    }

    :hover {
      background: none !important;
      color: ${colors.black};
    }

    span:nth-child(2) {
      color: ${colors.blue};
      margin-left: 3px;
      font-size: 14px;
      display: block;
      margin-top: -5px;
    }
  }

  li.active a {
    border-bottom: 5px solid;
    font-weight: 600;
  }
`;

export const ProposalPageTabs = ({ proposal, step, tabKey, votesCount }: Props) => {
  const showVotesTab = votesCount > 0 || proposal?.currentVotableStep !== null;
  const showFollowersTab = proposal?.project?.opinionCanBeFollowed;
  const voteTabLabel =
    step && isInterpellationContextFromProposal(proposal) ? 'global.support' : 'global.vote';
  const hasOfficialAnswer = proposal?.news?.edges
    ?.filter(Boolean)
    .map(edge => edge.node)
    .filter(Boolean)
    .some(e => e.title === 'Réponse officielle');
  const newsTotalCount = (proposal?.news.totalCount || 0) - (hasOfficialAnswer ? 1 : 0);

  return (
    <Tabs loading={!proposal}>
      <Nav>
        <NavItem disabled={!proposal} eventKey="content" active={tabKey === 'content'}>
          <FormattedMessage id="presentation_step" />
        </NavItem>
        {(newsTotalCount > 0 || !proposal) && (
          <NavItem disabled={!proposal} eventKey="blog" active={tabKey === 'blog'}>
            <FormattedMessage id="menu.news" />
            {proposal && <span className="tip">{newsTotalCount}</span>}
          </NavItem>
        )}
        {(showVotesTab || !proposal) && (
          <NavItem disabled={!proposal} eventKey="votes" active={tabKey === 'votes'}>
            <FormattedMessage id={voteTabLabel} />
            {proposal && <span className="tip">{votesCount}</span>}
          </NavItem>
        )}
        {(showFollowersTab || !proposal) && (
          <NavItem disabled={!proposal} eventKey="followers" active={tabKey === 'followers'}>
            <FormattedMessage id="proposal.tabs.followers" />
            {proposal && (
              <span className="tip">
                {proposal.allFollowers ? proposal.allFollowers.totalCount : 0}
              </span>
            )}
          </NavItem>
        )}
      </Nav>
    </Tabs>
  );
};

export default createFragmentContainer(ProposalPageTabs, {
  step: graphql`
    fragment ProposalPageTabs_step on ProposalStep {
      id
    }
  `,
  proposal: graphql`
    fragment ProposalPageTabs_proposal on Proposal @argumentDefinitions(stepId: { type: "ID!" }) {
      id
      form {
        usingCategories
        usingThemes
        isProposalForm
      }
      news {
        totalCount
        edges {
          node {
            id
            title
          }
        }
      }
      currentVotableStep {
        id
        voteThreshold
        voteType
      }
      votableSteps {
        id
        title
      }
      allFollowers: followers(first: 0) {
        totalCount
      }
      project {
        type {
          title
        }
        opinionCanBeFollowed
      }
    }
  `,
});
