// @flow
import * as React from 'react';
import Skeleton from '~ds/Skeleton';

export const MetadataPlaceHolder = ({
  ready,
  children,
}: {
  ready: boolean,
  children: React.Node,
}): React.Node => (
  <Skeleton isLoaded={ready} placeholder={<Skeleton.Text size="sm" width="150px" />}>
    {children}
  </Skeleton>
);
