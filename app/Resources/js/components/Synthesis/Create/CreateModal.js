import React from 'react';
import { IntlMixin } from 'react-intl';
import { Modal, Input, Button } from 'react-bootstrap';

import ElementsFinder from './../ElementsFinder';
import SynthesisElementActions from '../../../actions/SynthesisElementActions';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';

const CreateModal = React.createClass({
  propTypes: {
    synthesis: React.PropTypes.object,
    selectedId: React.PropTypes.string,
    elements: React.PropTypes.array,
    show: React.PropTypes.bool,
    toggle: React.PropTypes.func,
    process: React.PropTypes.func,
  },
  mixins: [IntlMixin, DeepLinkStateMixin],

  getDefaultProps() {
    return {
      process: null,
      selectedId: 'root',
    };
  },

  getInitialState() {
    const {
      elements,
      selectedId,
    } = this.props;
    return {
      name: null,
      parent: this.getElementInTreeById(elements, selectedId),
      expanded: this.getExpandedBasedOnSelectedId(),
      description: null,
    };
  },

  componentWillReceiveProps(nextProps) {
    const { selectedId } = this.props;
    if (nextProps.selectedId !== selectedId) {
      this.setState({
        parent: this.getElementInTreeById(nextProps.elements, nextProps.selectedId),
        expanded: this.getExpandedBasedOnSelectedId(),
      });
    }
  },

  getExpandedBasedOnSelectedId() {
    const {
      elements,
      selectedId,
    } = this.props;
    const expanded = {
      root: true,
    };
    if (elements && selectedId !== 'root') {
      expanded[selectedId] = true;
      const element = this.getElementInTreeById(elements, selectedId);
      if (element) {
        element.path.split(',').map((id) => {
          expanded[id] = true;
        });
      }
    }
    return expanded;
  },


  getElementInTreeById(elements, id) {
    for (let i = 0; i < elements.length; i++) {
      const element = elements[i];
      if (element.id === id) {
        return element;
      }
      if (element.children && element.children.length > 0) {
        const found = this.getElementInTreeById(element.children, id);
        if (found) {
          return found;
        }
      }
    }
    return null;
  },

  setName(event) {
    this.setState({
      name: event.target.value,
    });
  },

  setDescription(event) {
    this.setState({
      description: event.target.value,
    });
  },

  setParent(element) {
    if (element) {
      const value = element !== 'root' ? element : null;
      this.setState({
        parent: value,
      });
    }
  },

  expandItem(element) {
    const expanded = this.state.expanded;
    expanded[element.id] = !this.state.expanded[element.id];
    this.setState({
      expanded,
    });
  },

  show() {
    const { toggle } = this.props;
    toggle(true);
  },

  hide() {
    const { toggle } = this.props;
    this.setState({
      name: null,
      description: null,
    });
    toggle(false);
  },

  create() {
    const {
      process,
      synthesis,
    } = this.props;
    this.hide();
    const element = {
      archived: true,
      published: true,
      title: this.state.name,
      description: this.state.description,
      parent: this.state.parent,
    };
    if (typeof process === 'function') {
      process(element);
      return;
    }
    SynthesisElementActions.create(synthesis.id, element);
  },

  renderName() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.create.name.label')}`}
        </h2>
        <Input type="text" id="new_element_title" name="new_element[title]" className="new-element__title" placeholder={this.getIntlMessage('synthesis.edition.action.create.name.placeholder')} valueLink={this.linkState('name')} />
      </div>
    );
  },

  renderDescription() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.create.description.label')}`}
        </h2>
        <Input type="textarea" id="new_element_description" name="new_element[description]" className="new-element__title" valueLink={this.linkState('description')} />
      </div>
    );
  },

  renderParent() {
    return (
      <div className="modal__action">
        <h2 className="h4">
          {` ${this.getIntlMessage('synthesis.edition.action.create.parent.label')}`}
          <span className="small excerpt action__title-right">{`\t${this.getIntlMessage('synthesis.edition.action.create.optional')}`}</span>
        </h2>
        {this.renderParentFinder()}
      </div>
    );
  },

  renderParentFinder() {
    const {
      elements,
      synthesis,
    } = this.props;
    const parentId = this.state.parent ? this.state.parent.id : 'root';
    return (
      <ElementsFinder
        synthesis={synthesis}
        elements={elements}
        type="notIgnored"
        expanded={this.state.expanded}
        selectedId={parentId}
        onSelect={this.setParent}
        onExpand={this.expandItem}
      />
    );
  },

  render() {
    const { show } = this.props;
    return (
    <Modal show={show} onHide={this.hide} animation={false} dialogClassName="modal--create">
      <Modal.Header closeButton>
        <Modal.Title>{this.getIntlMessage('synthesis.edition.action.create.title')}</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        {this.renderName()}
        {this.renderDescription()}
        {this.renderParent()}
      </Modal.Body>
      <Modal.Footer>
        <Button type="button" onClick={this.hide.bind(null, this)}>{this.getIntlMessage('synthesis.edition.action.create.btn_cancel')}</Button>
        <Button type="submit" bsStyle="primary" onClick={this.create.bind(null, this)}>{this.getIntlMessage('synthesis.edition.action.create.btn_submit')}</Button>
      </Modal.Footer>
    </Modal>
    );
  },

});

export default CreateModal;
