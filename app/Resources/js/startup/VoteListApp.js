// @flow
import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import { QueryRenderer, graphql } from 'react-relay';
import IntlProvider from './IntlProvider';
import environment, { graphqlError } from '../createRelayEnvironment';
import type { VoteListAppQueryResponse } from '~relay/VoteListAppQuery.graphql';
import VoteListProfile from '../components/Vote/VoteListProfile';
import Loader from '../components/Ui/FeedbacksIndicators/Loader';

export default ({ userId }: { userId: string }) => (
  <Provider store={ReactOnRails.getStore('appStore')}>
    <IntlProvider>
      <QueryRenderer
        variables={{ userId, count: 5, cursor: null }}
        environment={environment}
        query={graphql`
          query VoteListAppQuery($userId: ID!, $count: Int, $cursor: String) {
            node(id: $userId) {
              id
              ...VoteListProfile_voteList @arguments(count: $count, cursor: $cursor)
            }
          }
        `}
        render={({
          error,
          props,
        }: {
          props: ?VoteListAppQueryResponse,
          ...ReactRelayReadyState,
        }) => {
          if (error) {
            return graphqlError;
          }

          if (!props) {
            return <Loader />;
          }

          return <VoteListProfile voteList={props.node} />;
        }}
      />
    </IntlProvider>
  </Provider>
);
