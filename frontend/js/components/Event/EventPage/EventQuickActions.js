// @flow
import * as React from 'react';
import { useIntl } from 'react-intl';
import { useSelector } from 'react-redux';
import { Text, CapUIIconSize, Menu, ButtonQuickAction, CapUIIcon } from '@cap-collectif/ui';
import useFeatureFlag from '~/utils/hooks/useFeatureFlag';
import type { GlobalState } from '~/types';
import EventDeleteModal from './EventDeleteModal';
import type { EventReviewStatus } from '~relay/EventPageHeader_query.graphql';

type Props = {|
  +id: string,
  +status: ?EventReviewStatus,
  +viewerDidAuthor: ?boolean,
|};

export const EventQuickActions = ({ id, status, viewerDidAuthor }: Props) => {
  const intl = useIntl();
  const hasProposeEventEnabled = useFeatureFlag('allow_users_to_propose_events');
  const isSuperAdmin = useSelector((state: GlobalState) =>
    state.user.user?.roles.includes('ROLE_SUPER_ADMIN'),
  );

  if (isSuperAdmin || (hasProposeEventEnabled && viewerDidAuthor && status === 'APPROVED'))
    return (
      <Menu
        disclosure={
          <ButtonQuickAction
            border="none"
            icon={CapUIIcon.More}
            size={CapUIIconSize.Md}
            variantColor="blue"
            label={intl.formatMessage({ id: 'global.plus' })}
            height="32px"
          />
        }>
        <Menu.List>
          {isSuperAdmin || viewerDidAuthor ? (
            <Menu.Item
              as="div"
              sx={{ cursor: 'pointer' }}
              onClick={() => window.open(`/export-my-event-participants/${id}`, '_self')}
              id="download-event-registration">
              <Text> {intl.formatMessage({ id: 'export-registered' })} </Text>
            </Menu.Item>
          ) : null}
          {status === 'APPROVED' || isSuperAdmin ? (
            <EventDeleteModal
              eventId={id}
              disclosure={
                <Menu.Item as="div" sx={{ cursor: 'pointer' }} closeOnSelect={false}>
                  <Text> {intl.formatMessage({ id: 'global.delete' })} </Text>
                </Menu.Item>
              }
            />
          ) : null}
        </Menu.List>
      </Menu>
    );
  return null;
};

export default EventQuickActions;
