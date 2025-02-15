// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import styled, { type StyledComponent } from 'styled-components';
import type { CommentEdit_comment } from '~relay/CommentEdit_comment.graphql';
import Icon, { ICON_NAME } from '~/components/Ui/Icons/Icon';
import colors from '~/utils/colors';

type Props = {|
  +comment: CommentEdit_comment,
|};

const CommentEditLink: StyledComponent<{}, {}, HTMLAnchorElement> = styled.a`
  display: flex;
  align-items: center;
  text-transform: uppercase;
  font-size: 12px;
  font-weight: 600;
  color: ${colors.darkGray};

  svg {
    margin-right: 5px;
  }
`;

export const CommentEdit = ({ comment }: Props) => {
  if (comment.contribuable && comment.author && comment.author.isViewer) {
    return (
      <CommentEditLink id={`CommentEdit-${comment.id}`} href={comment.editUrl}>
        <Icon name={ICON_NAME.pen} size={15} color={colors.darkGray} />
        <FormattedMessage id="global.change" />
      </CommentEditLink>
    );
  }
  return null;
};

export default createFragmentContainer(CommentEdit, {
  comment: graphql`
    fragment CommentEdit_comment on Comment
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      contribuable
      author {
        isViewer @include(if: $isAuthenticated)
      }
      editUrl
    }
  `,
});
