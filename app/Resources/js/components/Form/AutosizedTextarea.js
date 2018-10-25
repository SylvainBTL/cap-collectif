// @flow
import * as React from 'react';
import ReactDOM from 'react-dom';
import autosize from 'autosize';
import { FormControl } from 'react-bootstrap';

type Props = {};

class AutosizedTextarea extends React.Component<Props> {
  componentDidMount() {
    const input = ReactDOM.findDOMNode(this.refFormControl);
    autosize(input);
  }

  componentDidUpdate() {
    const input = ReactDOM.findDOMNode(this.refFormControl);
    autosize(input);
  }

  componentWillUnmount() {
    const input = ReactDOM.findDOMNode(this.refFormControl);
    autosize.destroy(input);
  }

  refFormControl: ?React.Component<*>;

  render() {
    console.log(this.props);

    return (
      <FormControl
        ref={c => {
          this.refFormControl = c;
        }}
        type="textarea"
        {...this.props}
      />
    );
  }
}

export default AutosizedTextarea;
