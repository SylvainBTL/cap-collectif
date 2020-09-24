// @flow
import * as React from 'react';
import { storiesOf } from '@storybook/react';
import Checkbox from '~/components/Ui/Form/Input/Checkbox/Checkbox';
import Radio from '~/components/Ui/Form/Input/Radio/Radio';
import TextArea from '~/components/Ui/Form/Input/TextArea/TextArea';
import Input from '~/components/Ui/Form/Input/Input';
import Dropzone from '~/components/Ui/Form/Dropzone/Dropzone';
import Toggle from '~/components/Ui/Toggle/Toggle';
import ColorPickerInput from '~/components/Form/ColorPickerInput/ColorPickerInput';

/* ------ INPUT ------ */

storiesOf('Core|Form/Input', module).add('Default', () => {
  return <Input type="text" id="1" name="test" value="Hello world" />;
});

storiesOf('Core|Form/Input', module).add('Disabled', () => {
  return <Input type="text" id="1" name="test" value="Hello world" disabled />;
});

storiesOf('Core|Form/Input', module).add('Number', () => {
  return <Input type="number" id="1" name="test" value={12} />;
});

/* ------ END INPUT ------ */

/* ------ CHECKBOX ------ */

storiesOf('Core|Form/Checkbox', module).add('Default', () => {
  return <Checkbox label="Je ne suis pas checked" id="1" name="test" value="test1" />;
});

storiesOf('Core|Form/Checkbox', module).add('Checked', () => {
  return <Checkbox label="Je suis checked" id="2" name="test" value="test2" checked />;
});

storiesOf('Core|Form/Checkbox', module).add('With image', () => {
  return (
    <Checkbox id="1" name="test" image="https://source.unsplash.com/random/300x200" value="test1" />
  );
});

storiesOf('Core|Form/Checkbox', module).add('With image checked', () => {
  return (
    <Checkbox
      id="1"
      name="test"
      image="https://source.unsplash.com/random/300x200"
      value="test1"
      checked
    />
  );
});

/* ------ END CHECKBOX ------ */

/* ------ RADIO ------ */

storiesOf('Core|Form/Radio', module).add('Default', () => {
  return <Radio label="Je ne suis pas checked" id="1" name="test" value="test1" />;
});

storiesOf('Core|Form/Radio', module).add('Checked', () => {
  return <Radio label="Je suis checked" id="2" name="test" value="test2" checked />;
});

storiesOf('Core|Form/Radio', module).add('With image', () => {
  return (
    <Radio id="1" name="test" image="https://source.unsplash.com/random/300x200" value="test1" />
  );
});

storiesOf('Core|Form/Radio', module).add('With image checked', () => {
  return (
    <Radio
      id="1"
      name="test"
      image="https://source.unsplash.com/random/300x200"
      value="test1"
      checked
    />
  );
});

/* ------ END RADIO ------ */

/* ------ RADIO BUTTON ------ */

storiesOf('Core|Form/RadioButton', module).add('Default', () => {
  return (
    <Radio
      label="Je ne suis pas checked"
      id="2"
      name="test"
      value="test2"
      color="#0AA900"
      isButton
    />
  );
});

storiesOf('Core|Form/RadioButton', module).add('Checked', () => {
  return (
    <Radio
      label="Je suis checked"
      id="2"
      name="test"
      value="test2"
      color="#0AA900"
      checked
      isButton
    />
  );
});

/* ------ END RADIO BUTTON ------ */

/* ------ TEXTAREA ------ */

storiesOf('Core|Form/TextArea', module).add('Default', () => {
  return <TextArea id="1" name="test" />;
});

storiesOf('Core|Form/TextArea', module).add('Disabled', () => {
  return <TextArea id="1" name="test" disabled />;
});

/* ------ END TEXTAREA ------ */

/* ------ DROPZONE ------ */

storiesOf('Core|Form/Dropzone', module).add('Default', () => {
  return (
    <Dropzone
      value={null}
      onChange={() => {}}
      setError={() => {}}
      errors={{
        DEFAULT: ['wording key', 'wording key'],
        MAX_FILE: ['wording key', 'wording key'],
      }}
    />
  );
});

/* ------ END DROPZONE ------ */

/* ------ TOGGLE ------ */

storiesOf('Core|Form/Toggle', module).add('Default', () => {
  return <Toggle id="1" name="test" value="test1" label="Toggler" />;
});

storiesOf('Core|Form/Toggle', module).add('Checked', () => {
  return <Toggle id="2" name="test" value="test2" label="Toggler" checked />;
});

storiesOf('Core|Form/Toggle', module).add('Disabled', () => {
  return <Toggle id="3" name="test" value="test3" label="Toggler" disabled />;
});

/* ------ END TOGGLE ------ */

/* ------ COLOR PICKER ------ */

storiesOf('Core|Form/ColorPicker', module).add('Default', () => {
  return <ColorPickerInput onChange={() => {}} placeholder="#ABCDEF" />;
});

storiesOf('Core|Form/ColorPicker', module).add('With value', () => {
  return <ColorPickerInput onChange={() => {}} value="#ABCDEF" />;
});

/* ------ END COLOR PICKER ------ */
