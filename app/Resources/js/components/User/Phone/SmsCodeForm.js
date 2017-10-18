import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { reduxForm, Field, SubmissionError } from 'redux-form';
import { connect } from 'react-redux';
import { Button } from 'react-bootstrap';
import renderComponent from '../../Form/Field';
// import Fetcher from '../../../services/Fetcher';
import UserActions from '../../../actions/UserActions';
// import FlashMessages from '../../Utils/FlashMessages';

type Props = {
  onSubmitSuccess: Function,
  submitting: boolean,
  handleSubmit?: Function,
};

const onSubmit = (values, dispatch, props) => {
  const { onSubmitSuccess } = props;

  console.warn(values.code);

  UserActions.sendSmsCode(values)
    .then(() => {
      onSubmitSuccess();
    })
    .catch(error => {
      if (error && error.response && error.response.message === 'sms_code_invalid') {
        throw new SubmissionError({ email: 'phone.confirm.code_invalid' });
      }
    });
};

const validate = ({ code }: Object) => {
  const errors = {};

  if (code === undefined || code.length !== 6) {
    errors.code = 'phone.confirm.constraints.code';
  }

  return errors;
};

export const formName = 'SmsCodeForm';

export class SmsCodeForm extends React.Component<Props> {
  // handleSubmit(e) {
  //   const { onSubmitSuccess } = this.props;
  //   e.preventDefault();
  //   if (this.isValid()) {
  //     const form = JSON.parse(JSON.stringify(this.state.form));
  //     this.setState({ isSubmitting: true });
  //     UserActions.sendSmsCode(form)
  //       .then(() => {
  //         this.setState(this.getInitialState());
  //         onSubmitSuccess();
  //       })
  //       .catch(error => {
  //         const response = error.response;
  //         const errors = this.state.errors;
  //         if (response.message === 'sms_code_invalid') {
  //           errors.code = ['phone.confirm.code_invalid'];
  //           this.setState({ errors, isSubmitting: false });
  //         }
  //       });
  //   }
  // }

  // renderFormErrors(field) {
  //   const errors = this.getErrorsMessages(field);
  //   if (errors.length === 0) {
  //     return null;
  //   }
  //   return <FlashMessages errors={errors} form />;
  // }

  render() {
    const { submitting, handleSubmit } = this.props;

    return (
      <form onSubmit={handleSubmit} style={{ maxWidth: '350px' }}>
        <Field
          type="text"
          name="code"
          autoFocus
          label={<FormattedMessage id="phone.confirm.code" />}
          id="_code"
          component={renderComponent}
        />
        <Button
          type="submit"
          bsStyle="primary"
          style={{ padding: '6px 12px 7px' }}
          disabled={submitting}>
          {submitting ? (
            <FormattedMessage id="global.loading" />
          ) : (
            <FormattedMessage id="phone.confirm.validate" />
          )}
        </Button>
      </form>
    );
  }
}

export default connect()(
  reduxForm({
    validate,
    onSubmit,
    form: formName,
  })(SmsCodeForm),
);
