import React from 'react';
import classNames from 'classnames';

export default class PaginationItem extends React.Component {
  render() {
    const classes = classNames({
      disabled: this.props.disabled,
      active: this.props.active,
    });
    return (
      <li className={classes}>
        <span className="page-item__wrapper" aria-label={this.props.ariaLabel || this.props.label || this.props.page} onClick={this.props.onSelect}>
          <span className="page-item__label" aria-hidden="true">
            {this.props.label || this.props.page}
          </span>
        </span>
      </li>
    );
  }
}

PaginationItem.propTypes = {
  page: React.PropTypes.number.isRequired,
  onSelect: React.PropTypes.func.isRequired,
  label: React.PropTypes.string,
  ariaLabel: React.PropTypes.string,
  disabled: React.PropTypes.bool,
  active: React.PropTypes.bool,
};

PaginationItem.defaultProps = {
  disabled: false,
  active: false,
  label: null,
  ariaLabel: null,
};

PaginationItem.displayName = 'PaginationItem';
