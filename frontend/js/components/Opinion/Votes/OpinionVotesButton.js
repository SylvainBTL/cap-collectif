// @flow
import * as React from 'react';
import ReactDOM from 'react-dom';
import cn from 'classnames';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage, injectIntl, type IntlShape } from 'react-intl';
import NewLoginOverlay from '../../Utils/NewLoginOverlay';
import UnpublishedTooltip from '../../Publishable/UnpublishedTooltip';
import FluxDispatcher from '../../../dispatchers/AppDispatcher';
import { VOTE_WIDGET_BOTH } from '../../../constants/VoteConstants';
import AddOpinionVoteMutation from '../../../mutations/AddOpinionVoteMutation';
import RemoveOpinionVoteMutation from '../../../mutations/RemoveOpinionVoteMutation';
import RequirementsFormModal from '../../Requirements/RequirementsModal';
import type { OpinionVotesButton_opinion } from '~relay/OpinionVotesButton_opinion.graphql';
import type { Dispatch } from '../../../types';

type RelayProps = {|
  +opinion: OpinionVotesButton_opinion,
|};

type YesNoPairedVoteValue = 'MITIGE' | 'NO' | 'YES';

const valueToObject = (value: YesNoPairedVoteValue): Object => {
  if (value === 'NO') {
    return {
      style: 'danger',
      str: 'nok',
      icon: 'cap cap-hand-unlike-2-1',
    };
  }
  if (value === 'MITIGE') {
    return {
      style: 'warning',
      str: 'mitige',
      icon: 'cap cap-hand-like-2-1 icon-rotate',
    };
  }
  return {
    style: 'success',
    str: 'ok',
    icon: 'cap cap-hand-like-2-1',
  };
};

type Props = {|
  ...RelayProps,
  dispatch: Dispatch,
  intl: IntlShape,
  +style: Object,
  +value: YesNoPairedVoteValue,
|};

type State = {|
  +isLoading: boolean,
  +showModal: boolean,
|};

export class OpinionVotesButton extends React.Component<Props, State> {
  static defaultProps = { style: {} };

  state = { isLoading: false, showModal: false };

  target = null;

  vote = () => {
    const { opinion, value } = this.props;
    if (opinion.__typename === 'Version' || opinion.__typename === 'Opinion') {
      const input = { opinionId: opinion.id, value };
      this.setState({ isLoading: true });
      AddOpinionVoteMutation.commit({ input })
        .then(res => {
          if (!res.addOpinionVote) {
            throw new Error('mutation failed');
          }
          FluxDispatcher.dispatch({
            actionType: 'UPDATE_ALERT',
            alert: {
              bsStyle: 'success',
              content: 'vote.add_success',
            },
          });
          this.setState({ isLoading: false });
        })
        .catch(() => {
          FluxDispatcher.dispatch({
            actionType: 'UPDATE_ALERT',
            alert: {
              bsStyle: 'danger',
              content: 'opinion.request.failure',
            },
          });
          this.setState({ isLoading: false });
        });
    }
  };

  deleteVote = () => {
    const { opinion } = this.props;
    if (opinion.__typename === 'Version' || opinion.__typename === 'Opinion') {
      const input = { opinionId: opinion.id };
      this.setState({ isLoading: true });
      RemoveOpinionVoteMutation.commit({ input })
        .then(res => {
          if (!res.removeOpinionVote) {
            throw new Error('mutation failed');
          }
          FluxDispatcher.dispatch({
            actionType: 'UPDATE_ALERT',
            alert: {
              bsStyle: 'success',
              content: 'vote.delete_success',
            },
          });
          this.setState({ isLoading: false });
        })
        .catch(() => {
          FluxDispatcher.dispatch({
            actionType: 'UPDATE_ALERT',
            alert: {
              bsStyle: 'danger',
              content: 'opinion.request.failure',
            },
          });
          this.setState({ isLoading: false });
        });
    }
  };

  voteAction = () => {
    const { opinion, value } = this.props;
    if (
      opinion.step &&
      opinion.step.requirements &&
      !opinion.step.requirements.viewerMeetsTheRequirements
    ) {
      this.openModal();
      return false;
    }
    const active = opinion.viewerVote && opinion.viewerVote.value === value;
    return active ? this.deleteVote() : this.vote();
  };

  voteIsEnabled = () => {
    const { opinion } = this.props;
    if (!opinion.section) {
      return false;
    }
    const voteType = opinion.section.voteWidgetType;
    if (voteType === VOTE_WIDGET_BOTH) {
      return true;
    }
    return false;
  };

  voteIsEnabled = () => {
    const { opinion } = this.props;
    if (!opinion.section) {
      return false;
    }
    const voteType = opinion.section.voteWidgetType;
    if (voteType === VOTE_WIDGET_BOTH) {
      return true;
    }
    return false;
  };

  openModal = () => {
    this.setState({ showModal: true });
  };

  closeModal = () => {
    this.setState({ showModal: false });
  };

  render() {
    const { intl, opinion, value, style } = this.props;
    const { isLoading, showModal } = this.state;
    if (
      !this.voteIsEnabled() ||
      (opinion.__typename !== 'Opinion' && opinion.__typename !== 'Version')
    ) {
      return null;
    }
    const disabled = !opinion.contribuable;
    const data = valueToObject(value);
    const active = opinion.viewerVote && opinion.viewerVote.value === value;
    return (
      <div>
        {opinion.step && (
          <RequirementsFormModal
            step={opinion.step}
            handleClose={this.closeModal}
            show={showModal}
          />
        )}
        <NewLoginOverlay>
          <button
            type="button"
            style={style}
            className={cn(`btn btn-${data.style} btn--outline`, { active: !!active })}
            onClick={this.voteAction}
            ref={button => {
              this.target = button;
            }}
            aria-label={intl.formatMessage({
              id: active ? `vote.aria_label_active.${data.str}` : `vote.aria_label.${data.str}`,
            })}
            disabled={disabled || isLoading}>
            {active && (
              <UnpublishedTooltip
                target={() => ReactDOM.findDOMNode(this.target)}
                publishable={opinion.viewerVote}
              />
            )}
            <i className={data.icon} /> <FormattedMessage id={`vote.${data.str}`} />
          </button>
        </NewLoginOverlay>
      </div>
    );
  }
}

export default createFragmentContainer(injectIntl(OpinionVotesButton), {
  opinion: graphql`
    fragment OpinionVotesButton_opinion on OpinionOrVersion
    @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      __typename
      ... on Opinion {
        id
        contribuable
        step {
          requirements {
            viewerMeetsTheRequirements @include(if: $isAuthenticated)
          }
          ...RequirementsFormLegacy_step @arguments(isAuthenticated: $isAuthenticated)

          ...RequirementsModal_step @arguments(isAuthenticated: $isAuthenticated)
        }
        section {
          voteWidgetType
        }
        viewerVote @include(if: $isAuthenticated) {
          id
          value
          ...UnpublishedTooltip_publishable
        }
      }
      ... on Version {
        id
        contribuable
        step {
          requirements {
            viewerMeetsTheRequirements @include(if: $isAuthenticated)
          }
          ...RequirementsFormLegacy_step @arguments(isAuthenticated: $isAuthenticated)
          ...RequirementsModal_step @arguments(isAuthenticated: $isAuthenticated)
        }
        section {
          voteWidgetType
        }
        viewerVote @include(if: $isAuthenticated) {
          id
          value
          ...UnpublishedTooltip_publishable
        }
        parent {
          id
        }
      }
    }
  `,
});
