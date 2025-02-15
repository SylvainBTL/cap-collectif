// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
// TODO https://github.com/cap-collectif/platform/issues/7774
// eslint-disable-next-line no-restricted-imports
import {
  Dropdown,
  MenuItem,
  Panel,
  ListGroup,
  ListGroupItem,
  Radio,
  OverlayTrigger,
} from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import FollowOpinionMutation from '../../../mutations/FollowOpinionMutation';
import UpdateFollowOpinionMutation from '../../../mutations/UpdateFollowOpinionMutation';
import type { OpinionFollowButton_opinion } from '~relay/OpinionFollowButton_opinion.graphql';
import UnfollowOpinionMutation from '../../../mutations/UnfollowOpinionMutation';
import NewLoginOverlay from '../../Utils/NewLoginOverlay';
import Popover from '../../Utils/Popover';

import type { SubscriptionTypeValue } from '~relay/UpdateFollowOpinionMutation.graphql';

type Props = {
  opinion: OpinionFollowButton_opinion,
};

type State = {
  isJustFollowed: boolean,
};

export class OpinionFollowButton extends React.Component<Props, State> {
  state = {
    isJustFollowed: false,
  };

  componentWillReceiveProps(nextProps: Props) {
    if (this.props !== nextProps) {
      this.setState({
        isJustFollowed: !!nextProps.opinion.viewerIsFollowing,
      });
    }
  }

  changeFollowType(opinion: OpinionFollowButton_opinion, type: SubscriptionTypeValue) {
    if (
      opinion.id &&
      opinion.viewerIsFollowing &&
      opinion.viewerFollowingConfiguration !== null &&
      typeof opinion.viewerFollowingConfiguration !== 'undefined'
    ) {
      return UpdateFollowOpinionMutation.commit({
        input: {
          opinionId: opinion.id,
          notifiedOf: type,
        },
      });
    }
  }

  render() {
    const { opinion } = this.props;
    const { isJustFollowed } = this.state;
    if (!opinion.viewerIsFollowing && opinion.id) {
      return (
        <NewLoginOverlay>
          <button
            type="button"
            className="btn btn-default opinion__button__follow mr-5"
            onClick={() =>
              opinion.id &&
              FollowOpinionMutation.commit({
                input: { opinionId: opinion.id, notifiedOf: 'MINIMAL' },
              })
            }
            id={`opinion-follow-btn-${opinion.id}`}>
            <i className="cap cap-rss" />
            <FormattedMessage id="follow" />
          </button>
        </NewLoginOverlay>
      );
    }
    if (
      opinion.id &&
      opinion.viewerFollowingConfiguration !== null &&
      typeof opinion.viewerFollowingConfiguration !== 'undefined'
    ) {
      return (
        <NewLoginOverlay>
          <span className="mb-0 custom-dropdown">
            <Dropdown
              className="mb-0 width250 custom-dropdown-bgd dropdown-button"
              id={`opinion-follow-btn-${opinion.id}`}
              defaultOpen={isJustFollowed}>
              <Dropdown.Toggle className="custom-dropdown-button">
                <i className="cap cap-rss" />
                <FormattedMessage id="following" />
              </Dropdown.Toggle>
              <Dropdown.Menu>
                <Panel className="mb-0 b-none width250">
                  <Panel.Heading>
                    <FormattedMessage id="to-be-notified-by-email" />
                    <OverlayTrigger
                      placement="top"
                      overlay={
                        <Popover placement="top" className="in" id="pinned-label">
                          <FormattedMessage id="you-will-receive-a-summary-of-your-notifications-once-a-day" />
                        </Popover>
                      }>
                      <span className="cap-information ml-30" />
                    </OverlayTrigger>
                  </Panel.Heading>
                  <Panel.Body>
                    <div className="b-none mb-0" id={`opinion-follow-btn-${opinion.id}`}>
                      <ListGroup className="mb-0">
                        <ListGroupItem>
                          <Radio
                            id={`opinion-follow-btn-minimal-${opinion.id}`}
                            name="minimal"
                            className="opinion__follow__minimal"
                            checked={
                              opinion.viewerFollowingConfiguration === 'MINIMAL' ? 'checked' : ''
                            }
                            inline
                            onChange={() => this.changeFollowType(opinion, 'MINIMAL')}>
                            <b>
                              <FormattedMessage id="essential" />
                            </b>{' '}
                            <br />
                            <FormattedMessage id="updates-and-news" />
                          </Radio>
                        </ListGroupItem>
                        <ListGroupItem>
                          <Radio
                            name="essential"
                            id={`opinion-follow-btn-essential-${opinion.id}`}
                            className="opinion__follow__essential"
                            checked={
                              opinion.viewerFollowingConfiguration === 'ESSENTIAL' ? 'checked' : ''
                            }
                            onChange={() => this.changeFollowType(opinion, 'ESSENTIAL')}>
                            <b>
                              <FormattedMessage id="intermediate" />
                            </b>
                            <br />
                            <FormattedMessage id="updates-news-and-new-contributions" />
                          </Radio>
                        </ListGroupItem>
                        <ListGroupItem>
                          <Radio
                            name="all"
                            id={`opinion-follow-btn-all-${opinion.id}`}
                            className="opinion__follow__all"
                            checked={
                              opinion.viewerFollowingConfiguration === 'ALL' ? 'checked' : ''
                            }
                            onChange={() => this.changeFollowType(opinion, 'ALL')}>
                            <b>
                              <FormattedMessage id="complete" />
                            </b>
                            <br />
                            <FormattedMessage id="updates-news-new-contributions-votes-and-subscriptions" />
                          </Radio>
                        </ListGroupItem>
                      </ListGroup>
                    </div>
                  </Panel.Body>
                </Panel>
                <MenuItem
                  eventKey="1"
                  className="opinion__unfollow"
                  id={`opinion-unfollow-btn-${opinion.id}`}
                  onClick={() => {
                    if (opinion.viewerIsFollowing) {
                      return UnfollowOpinionMutation.commit({
                        input: { opinionId: opinion.id },
                      });
                    }
                  }}>
                  <FormattedMessage id="unfollow" />
                </MenuItem>
              </Dropdown.Menu>
            </Dropdown>
          </span>
        </NewLoginOverlay>
      );
    }
    return null;
  }
}

export default createFragmentContainer(OpinionFollowButton, {
  opinion: graphql`
    fragment OpinionFollowButton_opinion on OpinionOrVersion
    @argumentDefinitions(isAuthenticated: { type: "Boolean", defaultValue: true }) {
      ... on Opinion {
        id
        viewerIsFollowing @include(if: $isAuthenticated)
        viewerFollowingConfiguration @include(if: $isAuthenticated)
      }
      ... on Version {
        id
        viewerIsFollowing @include(if: $isAuthenticated)
        viewerFollowingConfiguration @include(if: $isAuthenticated)
      }
    }
  `,
});
