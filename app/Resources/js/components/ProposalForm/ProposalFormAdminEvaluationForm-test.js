// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { ProposalFormAdminEvaluationForm } from './ProposalFormAdminEvaluationForm';

describe('<ProposalFormAdminEvaluationForm />', () => {
  const props = {
    handleSubmit: jest.fn(),
    invalid: false,
    valid: false,
    submitSucceeded: false,
    submitFailed: false,
    pristine: false,
    submitting: false,
    // $FlowFixMe $refType
    proposalForm: {
      id: 'proposalFormId',
      evaluationForm: null,
    },
  };

  it('render correctly', () => {
    const wrapper = shallow(<ProposalFormAdminEvaluationForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
