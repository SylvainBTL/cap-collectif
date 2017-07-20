import React, { PropTypes } from 'react';
import { Button } from 'react-bootstrap';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import RegistrationModal from './RegistrationModal';
import { showRegistrationModal } from '../../../redux/modules/user';
import type { State, Dispatch } from '../../../types';

export const RegistrationButton = React.createClass({
  propTypes: {
    features: PropTypes.object.isRequired,
    style: PropTypes.object,
    user: PropTypes.object,
    className: PropTypes.string,
    bsStyle: PropTypes.string,
    buttonStyle: PropTypes.object,
    openRegistrationModal: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      style: {},
      buttonStyle: {},
      user: null,
      className: '',
      bsStyle: 'primary',
    };
  },

  render() {
    const {
      bsStyle,
      buttonStyle,
      className,
      features,
      style,
      user,
      openRegistrationModal,
    } = this.props;
    if (!features.registration || !!user) {
      return null;
    }
    return (
      <span style={style}>
        <Button
          style={buttonStyle}
          onClick={openRegistrationModal}
          bsStyle={bsStyle}
          className={`btn--registration ${className}`}
        >
          { this.getIntlMessage('global.registration') }
        </Button>
        <RegistrationModal />
      </span>
    );
  },

});

const mapStateToProps = (state: State) => ({
  features: state.default.features,
  user: state.user.user,
});
const mapDispatchToProps = (dispatch: Dispatch) => ({
  openRegistrationModal: () => { dispatch(showRegistrationModal()); },
});

export default connect(mapStateToProps, mapDispatchToProps)(RegistrationButton);
