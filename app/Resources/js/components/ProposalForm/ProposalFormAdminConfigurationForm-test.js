// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { features } from '../../redux/modules/default';
import { ProposalFormAdminConfigurationForm } from './ProposalFormAdminConfigurationForm';

describe('<ProposalFormAdminConfigurationForm />', () => {
  const props = {
    intl: global.intlMock,
    proposalForm: {
      id: 'proposalFormId',
      description: 'description',
      usingThemes: true,
      themeMandatory: true,
      usingCategories: true,
      categoryMandatory: true,
      usingAddress: true,
      latMap: 0,
      lngMap: 0,
      zoomMap: 0,
      illustrationHelpText: '',
      addressHelpText: '',
      themeHelpText: '',
      categoryHelpText: '',
      descriptionHelpText: '',
      proposalInAZoneRequired: true,
      summaryHelpText: '',
      titleHelpText: '',
      usingDistrict: true,
      districtHelpText: '',
      districtMandatory: true,
      categories: [
        {
          id: 'category1',
          name: 'Category 1',
        },
      ],
      districts: [],
      questions: [
        {
          id: 'field-1',
          title: 'Titre 1',
          required: false,
          helpText: null,
          type: 'text',
          private: false,
          position: 1,
          kind: 'simple',
        },
      ],
    },
    handleSubmit: jest.fn(),
    invalid: false,
    pristine: false,
    submitting: false,
    usingAddress: true,
    usingCategories: true,
    usingThemes: true,
    usingDistrict: true,
    features,
  };

  it('render correctly', () => {
    const wrapper = shallow(<ProposalFormAdminConfigurationForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
