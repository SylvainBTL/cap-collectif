// @flow
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { LeafletMap } from './LeafletMap';

describe('<LeafletMap />', () => {
  const defaultMapOptions = {
    center: { lat: 48.8586047, lng: 2.3137325 },
    zoom: 12,
  };

  const markers = {
    marker: {
      edges: [
        {
          node: {
            id: 'event1',
            lat: 47.12345789,
            lng: 1.23456789,
            url: 'http://perdu.com',
            address: 'Ici et ailleur',
            startAt: '2018-09-27T03:00:00+01:00',
            endAt: '2019-09-27T03:00:00+01:00',
            title: 'Evenement des gens perdu',
            author: {
              username: 'toto',
              media: {
                url: 'http://monimage.toto',
              },
              url: 'http://jesuistoto.fr',
            },
          },
        },
        {
          node: {
            id: 'event2',
            lat: 47.1235444789,
            lng: 1.23477789,
            url: 'http://perdu.com',
            address: 'Nul part et ailleur',
            startAt: '2018-10-07T03:00:00+01:00',
            endAt: '2019-10-27T03:00:00+01:00',
            title: 'Evenement des gens pas perdu',
            author: {
              username: 'toto',
              media: {
                url: 'http://monimage.toto',
              },
              url: 'http://jesuistoto.fr',
            },
          },
        },
      ],
    },
  };

  const props = {
    dispatch: jest.fn(),
    eventSelected: 'event2',
    mapTokens: {
      MAPBOX: {
        initialPublicToken:
          '***REMOVED***',
        publicToken:
          '***REMOVED***',
        styleOwner: 'capcollectif',
        styleId: '***REMOVED***',
      },
    },
  };

  it('should render a map with markers', () => {
    const wrapper = shallow(
      <LeafletMap defaultMapOptions={defaultMapOptions} {...props} markers={markers} />,
    );
    wrapper.setState({ loaded: false });
    expect(wrapper).toMatchSnapshot();
  });
});
