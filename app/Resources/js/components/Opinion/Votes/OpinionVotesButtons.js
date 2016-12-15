// @flow
import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { ButtonToolbar } from 'react-bootstrap';
import { connect } from 'react-redux';
import OpinionVotesButton from './OpinionVotesButton';

const OpinionVotesButtons = React.createClass({
  propTypes: {
    opinion: PropTypes.object.isRequired,
    show: PropTypes.bool.isRequired,
    disabled: PropTypes.bool.isRequired,
    user: PropTypes.object,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      user: null,
    };
  },

  isTheUserTheAuthor() {
    const {
      opinion,
      user,
    } = this.props;
    if (opinion.author === null || !user) {
      return false;
    }
    return user.uniqueId === opinion.author.uniqueId;
  },

  render() {
    const {
      opinion,
      disabled,
      show,
    } = this.props;
    if (!show) {
      return null;
    }
    return (
      <ButtonToolbar className="opinion__votes__buttons">
        <OpinionVotesButton disabled={disabled} opinion={opinion} value={1} />
        <OpinionVotesButton disabled={disabled} style={{ marginLeft: 5 }} opinion={opinion} value={0} />
        <OpinionVotesButton disabled={disabled} style={{ marginLeft: 5 }} opinion={opinion} value={-1} />
      </ButtonToolbar>
    );
  },

});

const mapStateToProps = state => ({
  user: state.default.user,
});

export default connect(mapStateToProps)(OpinionVotesButtons);
