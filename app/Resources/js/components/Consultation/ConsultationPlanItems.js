// @flow
import * as React from 'react';
import {Collapse, Nav} from 'react-bootstrap';
import ConsultationPlanItem from './ConsultationPlanItem';

type Props = {
  section: Object,
  level: number,
};

type State = {
  isOpen: boolean,
};

export class ConsultationPlanItems extends React.Component<Props, State> {
  state = {
    isOpen: false,
  };

  render() {
    const { section, level } = this.props;

    return (
      <Nav bsStyle="pills" stacked>
        <ConsultationPlanItem section={section} level={level} onCollapse={(activeItem) => {this.setState({ isOpen: activeItem })}}/>
        <Collapse in={this.state.isOpen}>
          <div>
            {section.sections &&
            section.sections.map((subSelection, index) => (
              <ConsultationPlanItems key={index} section={subSelection} level={level + 1} />
            ))}
          </div>
        </Collapse>
      </Nav>
    );
  }
}

export default ConsultationPlanItems;
