/* eslint-env jest */
// @flow
import React from 'react';
import { shallow } from 'enzyme';
import { ProposalPage } from './ProposalPage';
import { disableFeatureFlags } from '~/testUtils';

describe('<ProposalPage />', () => {
  const props = {
    proposalSlug: 'proposal-titre',
    currentVotableStepId: 'stepid',
    isAuthenticated: false,
  };

  afterEach(() => {
    disableFeatureFlags();
  });

  it('should render a proposal page', () => {
    const wrapper = shallow(<ProposalPage currentVotableStepId={null} {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
