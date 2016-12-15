import React, { PropTypes } from 'react';
import { Modal, Alert } from 'react-bootstrap';
import { IntlMixin, FormattedHTMLMessage } from 'react-intl';
import { connect } from 'react-redux';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';
import RegistrationForm from './RegistrationForm';
import { LoginSocialButtons } from '../Login/LoginSocialButtons';

export const RegistrationModal = React.createClass({
  propTypes: {
    show: PropTypes.bool.isRequired,
    onClose: PropTypes.func.isRequired,
    features: PropTypes.object.isRequired,
    parameters: PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      isSubmitting: false,
    };
  },

  handleSubmit() {
    this.setState({ isSubmitting: true });
    this.form.getWrappedInstance().form.submit();
  },

  stopSubmit() {
    this.setState({ isSubmitting: false });
  },

  handleSubmitSuccess() {
    const { onClose } = this.props;
    this.stopSubmit();
    onClose();
  },

  render() {
    const { isSubmitting } = this.state;
    const {
      onClose,
      show,
      parameters,
      features,
    } = this.props;
    const textTop = parameters['signin.text.top'];
    const textBottom = parameters['signin.text.bottom'];
    return (
      <Modal
        animation={false}
        show={show}
        onHide={onClose}
        bsSize="small"
        aria-labelledby="contained-modal-title-lg"
        enforceFocus={false}
      >
        <Modal.Header closeButton>
          <Modal.Title id="contained-modal-title-lg">
            {this.getIntlMessage('global.register')}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {
            textTop &&
            <Alert bsStyle="info" className="text-center">
              <FormattedHTMLMessage message={textTop} />
            </Alert>
          }
          <LoginSocialButtons
            features={{
              login_facebook: features.login_facebook,
              login_gplus: features.login_gplus,
            }}
            prefix="registration."
          />
          <RegistrationForm
            ref={c => this.form = c}
            onSubmitFail={this.stopSubmit}
            onSubmitSuccess={this.handleSubmitSuccess}
          />
          {
            textBottom &&
            <div className="text-center small excerpt" style={{ marginTop: '15px' }}>
              <FormattedHTMLMessage message={textBottom} />
            </div>
          }
        </Modal.Body>
        <Modal.Footer>
          <CloseButton onClose={onClose} />
          <SubmitButton
            id="confirm-register"
            label="global.register"
            isSubmitting={isSubmitting}
            onSubmit={this.handleSubmit}
          />
        </Modal.Footer>
      </Modal>
    );
  },

});

const mapStateToProps = (state) => {
  return {
    features: state.default.features,
    parameters: state.default.parameters,
  };
};

export default connect(mapStateToProps)(RegistrationModal);
