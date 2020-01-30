// @flow
import * as React from 'react';
import { storiesOf } from '@storybook/react';
import { arrayObject, color, number, boolean } from 'storybook-addon-knobs';
import Calendar from '../../components/Ui/Calendar/Calendar';

const inputs = [
  {
    title: 'Lancement de la concertation',
    date: 'Jeudi 6 février',
  },
  {
    title: 'Première revue des participations et identification des tendances',
    date: 'Mars 2020',
  },
  {
    title: 'Fin des contributions sur le terrain et sur la plateforme',
    date: 'Avril 2020',
  },
  {
    title: 'Restitution de la concertation',
    date: 'Juin 2020',
  },
];

storiesOf('Cap Collectif|Calendar', module)
  .add(
    'Default',
    () => (
      <div style={{ maxWidth: 1000, margin: 'auto' }}>
        <Calendar
          defaultColor={color('defaultColor', '#1C671C')}
          backgroundColor={color('backgroundColor', '#FFF')}
          inputs={arrayObject('inputs', inputs)}
        />
      </div>
    ),
    {
      knobsToBo: {
        componentName: 'CalendarApp',
      },
    },
  )
  .add(
    'With Styled Border',
    () => (
      <div style={{ maxWidth: 1000, margin: 'auto' }}>
        <Calendar
          withBorder={boolean('withBorder', true)}
          defaultColor={color('defaultColor', '#EE6132')}
          backgroundColor={color('backgroundColor', '#FFF')}
          inputs={arrayObject('inputs', inputs)}
        />
      </div>
    ),
    {
      knobsToBo: {
        componentName: 'CalendarApp',
      },
    },
  )
  .add(
    'With Active Color',
    () => (
      <div style={{ maxWidth: 1000, margin: 'auto', background: '#F3F3F3', padding: '20px' }}>
        <Calendar
          defaultColor={color('defaultColor', '#1E336E')}
          activeColor={color('activeColor', '#FA0183')}
          activeNumber={number('activeNumber', 1)}
          backgroundColor={color('backgroundColor', '#F3F3F3')}
          withBorder={boolean('withBorder', false)}
          inputs={arrayObject('inputs', inputs)}
        />
      </div>
    ),
    {
      knobsToBo: {
        componentName: 'CalendarApp',
      },
    },
  );
