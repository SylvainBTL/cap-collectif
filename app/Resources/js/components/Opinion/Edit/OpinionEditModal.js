import React, { PropTypes } from 'react';
import { Modal } from 'react-bootstrap';
import { IntlMixin } from 'react-intl';
import OpinionEditForm from '../Form/OpinionEditForm';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';

const OpinionEditModal = React.createClass({
  propTypes: {
    show: PropTypes.bool.isRequired,
    opinion: PropTypes.object.isRequired,
    onClose: PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      isSubmitting: false,
    };
  },

  handleSubmit() {
    if (this.form.isValid()) {
      this.form.submit();
      this.setState({ isSubmitting: true });
    }
  },

  handleSubmitSuccess() {
    const { onClose } = this.props;
    this.setState({ isSubmitting: false });
    onClose();
  },

  stopSubmit() {
    this.setState({ isSubmitting: false });
  },

  render() {
    const { isSubmitting } = this.state;
    const { onClose, show, opinion } = this.props;
    return (
      <Modal
        animation={false}
        show={show}
        onHide={() => {
          if (window.confirm(this.getIntlMessage('proposal.confirm_close_modal'))) { // eslint-disable-line no-alert
            onClose();
          }
        }}
        bsSize="large"
        aria-labelledby="contained-modal-title-lg"
      >
        <Modal.Header closeButton>
          <Modal.Title id="contained-modal-title-lg">
            { this.getIntlMessage('global.edit') }
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <OpinionEditForm
            ref={c => this.form = c}
            opinion={opinion}
            onSubmitSuccess={this.handleSubmitSuccess}
            onFailure={this.stopSubmit}
          />
        </Modal.Body>
        <Modal.Footer>
          <CloseButton onClose={onClose} />
          <SubmitButton
            label="global.edit"
            id={'confirm-opinion-update'}
            isSubmitting={isSubmitting}
            onSubmit={this.handleSubmit}
          />
        </Modal.Footer>
      </Modal>
    );
  },

});

export default OpinionEditModal;
