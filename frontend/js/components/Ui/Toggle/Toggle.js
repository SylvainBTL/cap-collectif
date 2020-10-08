// @flow
import * as React from 'react';
import { type Node } from 'react';
import { FormattedMessage } from 'react-intl';
import { Button, Overlay } from 'react-bootstrap';
import classNames from 'classnames';
import type { PropsCommonCheckboxRadio } from '~ui/Form/Input/commonCheckboxRadio';
import {
  ToggleContainer,
  TogglerWrapper,
  LabelContainer,
  TooltipContent,
  TooltipFooter,
  CloseButton,
  PopoverContainer,
  type LabelSide,
} from '~ui/Toggle/Toggle.style';
import Icon, { ICON_NAME } from '~ui/Icons/Icon';
import colors from '~/utils/colors';

export type Tooltip = {| content: React.Node, width?: string |};

type Props = {|
  ...PropsCommonCheckboxRadio,
  id?: string,
  name?: string,
  roledescription?: Node | string,
  value?: string,
  checked?: ?boolean,
  labelSide?: LabelSide,
  tooltip?: ?Tooltip,
  bold?: boolean,
|};

const Toggle = ({
  label,
  className,
  id,
  name,
  value,
  onChange,
  onBlur,
  disabled = false,
  checked = false,
  labelSide = 'RIGHT',
  tooltip,
  roledescription,
  bold,
}: Props) => {
  const referenceToggle = React.useRef(null);
  const [isTooltipShow, setIsTooltipShow] = React.useState<boolean>(false);

  const handleChange = e => {
    const { checked: isChecked } = e.target;

    if (!isChecked && tooltip) {
      return setIsTooltipShow(true);
    }

    if (onChange) onChange(e, isChecked);
  };

  const classes = classNames({
    checked,
    unchecked: !checked,
  });

  return (
    <ToggleContainer className={className}>
      <div>
        <LabelContainer
          className={classNames(classes)}
          disabled={disabled}
          htmlFor={id}
          labelSide={labelSide}
          bold={bold}>
          <TogglerWrapper disabled={disabled} checked={checked} ref={referenceToggle}>
            <span className="circle-toggler" />

            {tooltip && (
              <Overlay show={isTooltipShow} placement="top" target={referenceToggle.current}>
                <PopoverContainer id="tooltip-toggle" width={tooltip.width}>
                  <TooltipContent>
                    {tooltip.content}

                    <CloseButton type="button" onClick={() => setIsTooltipShow(false)}>
                      <Icon name={ICON_NAME.close} size={12} color={colors.darkGray} />
                    </CloseButton>
                  </TooltipContent>

                  <TooltipFooter>
                    <Button onClick={() => setIsTooltipShow(false)} className="btn-cancel">
                      <FormattedMessage id="global.cancel" />
                    </Button>
                    <Button
                      onClick={() => {
                        if (onChange) onChange(null, false);
                        setIsTooltipShow(false);
                      }}
                      className="btn-confirm">
                      <FormattedMessage id="action_disable" />
                    </Button>
                  </TooltipFooter>
                </PopoverContainer>
              </Overlay>
            )}
          </TogglerWrapper>

          {label && <span className="label-toggler">{label}</span>}
        </LabelContainer>

        <input
          type="checkbox"
          checked={checked}
          id={id}
          onChange={e => handleChange(e)}
          onBlur={onBlur}
          name={name}
          value={value}
          disabled={disabled}
        />
      </div>
      {roledescription && <span className="excerpt">{roledescription}</span>}
    </ToggleContainer>
  );
};

export default Toggle;
