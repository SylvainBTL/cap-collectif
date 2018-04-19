// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import type { ProposalPreviewFooter_proposal } from './__generated__/ProposalPreviewFooter_proposal.graphql'

type Props = {
  proposal: ProposalPreviewFooter_proposal,
};

export class ProposalPreviewFooter extends React.Component<Props> {

  render() {
    const { proposal } = this.props;

    const showComments= true;
    const showVotes = true;

    if (!showVotes && !showComments) {
      return null;
    }

    const countersClasses = {};

    if (showVotes && showComments) {
      countersClasses[`card__counters_multiple`] = true;
    }

    return (
      <div className={`card__counters ${classNames(countersClasses)}`}>
        {showComments && (
          <div className="card__counter card__counter-comments">
            <div className="card__counter__value">{proposal.commentsCount}</div>
            <div>
              <FormattedMessage
                id="comment.count_no_nb"
                values={{
                  count: proposal.commentsCount,
                }}
              />
            </div>
          </div>
        )}
        {showVotes && (
          <div className="card__counter card__counter-votes">
            <div className="card__counter__value">{proposal.votes.totalCount}</div>
            <div>
              <FormattedMessage
                id="proposal.vote.count_no_nb"
                values={{
                  count: proposal.votes.totalCount,
                }}
              />
            </div>
          </div>
        )}
      </div>
    );
  }
}

export default createFragmentContainer(
  ProposalPreviewFooter,
  {
    proposal: graphql`
      fragment ProposalPreviewFooter_proposal on Proposal
      @argumentDefinitions(stepId: { type: "ID!", nonNull: true })
      {
        id
        commentsCount
        votes(stepId: $stepId) {
          totalCount
        }
      }
    `,
  }
);
