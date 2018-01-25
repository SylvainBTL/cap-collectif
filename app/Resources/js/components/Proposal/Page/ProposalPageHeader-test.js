/* eslint-env jest */
// @flow
import React from 'react';
import { shallow } from 'enzyme';
import { ProposalPageHeader } from './ProposalPageHeader';

describe('<ProposalPageHeader />', () => {
  // $FlowFixMe $refType
  const proposal = {
    title: 'titre',
    theme: {
      title: 'titre du theme',
    },
    author: {
      username: 'userAdmin',
      displayName: 'userAdmin',
      media: {
        url: 'http://media.url',
      },
    },
    createdAt: '2015-01-01 00:00:00',
    updatedAt: '2015-01-05 00:00:00',
    publicationStatus: 'PUBLISHED',
    show_url: 'true',
  };
  // $FlowFixMe $refType
  const proposalWithoutTheme = {
    title: 'titre',
    theme: null,
    author: {
      username: 'userAdmin',
      displayName: 'userAdmin',
      media: {
        url: 'http://media.url',
      },
    },
    createdAt: '2015-01-01 00:00:00',
    updatedAt: '2015-01-05 00:00:00',
    publicationStatus: 'PUBLISHED',
    show_url: 'true',
  };

  const props = {
    className: '',
    referer: 'http://capco.test',
    oldProposal: {
      selections: [],
      votesByStepId: {
        selectionstep1: [],
        collectstep1: [],
      },
      votableStepId: 'selectionstep1',
      votesCountByStepId: {
        selectionstep1: 0,
        collectstep1: 0,
      },
      isDraft: false,
      viewerCanSeeEvaluation: true,
    },
  };

  it('should render a proposal header', () => {
    /* $FlowFixMe https://github.com/cap-collectif/platform/issues/4973 */
    const wrapper = shallow(<ProposalPageHeader proposal={proposal} {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should not render theme if proposal has none', () => {
    /* $FlowFixMe https://github.com/cap-collectif/platform/issues/4973 */
    const wrapper = shallow(<ProposalPageHeader proposal={proposalWithoutTheme} {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
