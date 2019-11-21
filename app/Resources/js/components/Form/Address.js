// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { change } from 'redux-form';
import PlacesAutocomplete, { geocodeByAddress } from 'react-places-autocomplete';
import type { Dispatch } from '../../types';
import invariant from '../../utils/invariant';

type PassedProps = {
  onChange: Function,
  value: any,
  id: string,
  placeholder: string,
  formName: string,
};
type Props = PassedProps & { updateAddressValue: (value: ?string) => void };

const renderSuggestion = ({
  formattedSuggestion,
}: {
  formattedSuggestion: { mainText: string, secondaryText: string },
}) => (
  <div className="places-autocomplete">
    <strong>{formattedSuggestion.mainText}</strong> {formattedSuggestion.secondaryText}
  </div>
);

class Address extends React.Component<Props> {
  resetAddressField = () => {
    const { onChange, updateAddressValue } = this.props;
    onChange(null);
    updateAddressValue(null);
  };

  handleAddressChange = (address: ?string) => {
    const { onChange, updateAddressValue } = this.props;
    geocodeByAddress(address)
      .then(results => {
        const addressToSend = JSON.stringify(results);
        const addressText = results[0].formatted_address;
        onChange(addressText);
        updateAddressValue(addressToSend);
      })
      .catch(() => {
        this.resetAddressField();
        invariant(false, 'Google places error.');
      });
  };

  render() {
    const { placeholder, value, id, onChange } = this.props;
    return (
      <PlacesAutocomplete
        inputProps={{
          onChange: (address: ?string) => {
            onChange(address);
          },
          placeholder,
          value,
          type: 'text',
          id,
        }}
        renderSuggestion={renderSuggestion}
        onEnterKeyDown={this.handleAddressChange}
        onSelect={this.handleAddressChange}
        onError={() => {
          this.resetAddressField();
        }}
        classNames={{
          input: 'form-control',
          autocompleteContainer: 'autocompleteContainer',
          autocompleteItem: 'autocompleteItem',
          autocompleteItemActive: 'autocompleteItemActive',
        }}
      />
    );
  }
}

const mapDispatchToProps = (dispatch: Dispatch, props: PassedProps) => ({
  updateAddressValue: value => {
    dispatch(change(props.formName, 'address', value));
  },
});

const connector = connect(
  null,
  mapDispatchToProps,
);

export default connector(Address);
