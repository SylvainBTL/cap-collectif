// @flow
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Alert } from 'react-bootstrap';
import LoginSocialButtons from './LoginSocialButtons';
import LoginForm from './LoginForm';
import type { State } from '../../../types';
import WYSIWYGRender from '../../Form/WYSIWYGRender';

type Props = {|
  textTop: string,
  textBottom: string,
  byPassAuth: boolean,
|};

export class LoginBox extends Component<Props> {
  render() {
    const { textTop, textBottom, byPassAuth } = this.props;
    return (
      <div>
        {textTop && (
          <Alert bsStyle="info" className="text-center">
            <WYSIWYGRender value={textTop} />
          </Alert>
        )}
        <LoginSocialButtons />
        {!byPassAuth && <LoginForm />}
        {textBottom && <WYSIWYGRender className="text-center excerpt mt-15" value={textBottom} />}
      </div>
    );
  }
}

const mapStateToProps = (state: State) => ({
  textTop: state.default.parameters['login.text.top'],
  textBottom: state.default.parameters['login.text.bottom'],
  byPassAuth: state.default.features.sso_by_pass_auth || false,
});

export default connect(mapStateToProps)(LoginBox);
