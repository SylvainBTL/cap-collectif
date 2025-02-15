// @flow
import * as React from 'react';
import AppBox from './AppBox';
import { LineHeight } from '~ui/Primitives/constants';
import type { AppBoxProps } from '~ui/Primitives/AppBox.type';
import jsxInnerText from '~/utils/jsxInnerText';

export type TextProps = {|
  ...AppBoxProps,
  +truncate?: number,
|};

// typings is handled by the .d.ts file
const Text = React.forwardRef<TextProps, HTMLElement>(
  ({ truncate, children, ...props }: TextProps, ref) => {
    let content = children;
    const innerText = jsxInnerText(content);
    if (truncate && innerText.length > truncate) {
      content = `${innerText.slice(0, truncate)}…`;
    }
    return (
      <AppBox
        ref={ref}
        as="p"
        fontFamily="body"
        lineHeight={LineHeight.Base}
        m={0}
        {...(truncate && { title: innerText })}
        {...props}>
        {content}
      </AppBox>
    );
  },
);

Text.displayName = 'Text';

export default Text;
