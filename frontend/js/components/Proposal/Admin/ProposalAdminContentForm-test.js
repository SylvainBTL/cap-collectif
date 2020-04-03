// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { ProposalAdminContentForm, checkProposalContent } from './ProposalAdminContentForm';
import { features } from '../../../redux/modules/default';
import { $refType, $fragmentRefs, intlMock, formMock } from '../../../mocks';

describe('<ProposalAdminContentForm />', () => {
  const values = {
    media: null,
    responses: [],
    draft: true,
    title: 'some title',
    body: null,
    summary: null,
    author: { id: 'toto', label: 'Toto', value: 'toto' },
    theme: null,
    addresstext: null,
    category: null,
    district: null,
    address: null,
  };

  const props = {
    ...formMock,
    features,
    handleSubmit: jest.fn(),
    intl: intlMock,
    isAdmin: true,
    themes: [
      { id: 'theme-1', title: 'Theme 1' },
      { id: 'theme-2', title: 'Theme 2' },
    ],
    proposal: {
      $refType,
      $fragmentRefs,
      id: '1',
      title: 'title-1',
      summary: 'summary',
      body: 'body',
      publicationStatus: 'DRAFT',
      responses: [
        { question: { id: '1' }, value: 'value-1' },
        {
          question: { id: '2' },
          medias: [{ id: '1', name: 'media-1', size: '100', url: '' }],
        },
      ],
      mergedIn: [],
      mergedFrom: [
        { id: '1', title: 'Child 1', adminUrl: 'http://capco.dev/child1' },
        { id: '2', title: 'Child 2', adminUrl: 'http://capco.dev/child2' },
      ],
      media: { id: '1', url: '' },
      form: {
        id: 'form1',
        description: 'Description',
        districts: [],
        step: {
          id: 'step1',
          form: {
            isProposalForm: true,
          },
          project: {
            type: {
              title: 'global.consultation',
            },
          },
        },
        categories: [
          { id: '1', name: 'category-1' },
          { id: '2', name: 'category-2' },
        ],
        questions: [
          {
            id: '1',
            title: 'title',
            type: 'text',
            helpText: 'Help 1',
            description: null,
            position: 0,
            number: 1,
            jumps: [],
            alwaysJumpDestinationQuestion: null,
            private: false,
            required: true,
            validationRule: null,
            __typename: 'SimpleQuestion',
            choices: {
              pageInfo: {
                hasNextPage: false,
              },
              totalCount: 0,
              edges: [],
            },
            isOtherAllowed: false,
          },
        ],
        usingDistrict: true,
        districtMandatory: true,
        districtHelpText: 'District Help',
        usingThemes: true,
        usingDescription: true,
        usingSummary: true,
        themeMandatory: true,
        descriptionMandatory: true,
        usingCategories: true,
        categoryMandatory: true,
        categoryHelpText: 'Category Help',
        usingAddress: true,
        titleHelpText: 'Title help',
        summaryHelpText: 'Summary Help',
        themeHelpText: 'Theme Help',
        illustrationHelpText: 'Illustration Help',
        descriptionHelpText: 'Description Help',
        addressHelpText: 'Address Help',
        proposalInAZoneRequired: true,
        usingIllustration: true,
        suggestingSimilarProposals: true,
        isProposalForm: true,
      },
      author: {
        id: '1',
        displayName: 'Author',
      },
      theme: { id: 'theme-1' },
      category: { id: '1' },
      address: null,
      district: { id: '1' },
    },
    responses: [],
  };

  it('render correctly', () => {
    const wrapper = shallow(<ProposalAdminContentForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('doest not allow title > 255 chars', () => {
    expect(
      checkProposalContent(
        {
          ...values,
          title:
            'bonjour-monsieur-quand-le-minimum-contributif-va-til-etre-revaloriser-significativement-ou-alors-nous-donner-laspa-qui-va-etre-a-900-euros-notre-complementaire-nous-lavons-grace-a-notre-travail-on-devrait-avoir-laspa-notre-complementaire-car-le-minimum-co',
        },
        { ...props.proposal.form, $refType },
        features,
        intlMock,
        true,
      ).title,
    ).toMatchInlineSnapshot(`"question.title.max_length"`);
  });
});
