// @flow
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { EventAdminFormPage } from './EventAdminFormPage';
import { intlMock, $refType, $fragmentRefs } from '../../../../mocks';

const defaultProps = {
  intl: intlMock,
  pristine: true,
  valid: true,
  submitting: true,
  submitSucceeded: true,
  submitFailed: true,
  invalid: true,
  dispatch: jest.fn(),
  event: {
    id: 'event1',
    author: {
      username: 'toto',
    },
    $fragmentRefs,
    $refType,
  },
  query: {
    $fragmentRefs,
    $refType,
  },
  isSuperAdmin: true,
  viewer: {
    username: 'toto',
  },
};

describe('<EventAdminFormPage />', () => {
  it('it renders correctly, with props at true', () => {
    const props = {
      ...defaultProps,
    };
    const wrapper = shallow(<EventAdminFormPage {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('it renders correctly, with props at false', () => {
    const props = {
      ...defaultProps,
      pristine: false,
      valid: false,
      submitting: false,
      submitSucceeded: false,
      submitFailed: false,
      invalid: false,
      isSuperAdmin: false,
    };
    const wrapper = shallow(<EventAdminFormPage {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('it renders correctly, without event', () => {
    const props = {
      ...defaultProps,
      event: null,
      viewer: {
        username: 'notToto',
      },
    };
    const wrapper = shallow(<EventAdminFormPage {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
