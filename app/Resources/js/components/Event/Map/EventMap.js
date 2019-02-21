// @flow
import * as React from 'react';
import { graphql, createPaginationContainer, type RelayPaginationProp } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { Button } from 'react-bootstrap';
import LeafletMap from './LeafletMap';
import type { EventMap_query } from './__generated__/EventMap_query.graphql';

type Props = {|
  query: EventMap_query,
  relay: RelayPaginationProp,
|};

type State = {|
  loading: boolean,
|};

const PAGINATION = 250;

export class EventMap extends React.Component<Props, State> {
  state = {
    loading: false,
  };

  loadMore = () => {
    const { relay, query } = this.props;
    if (!query.events.pageInfo.hasNextPage) {
      this.setState({ loading: false });
      return;
    }
    relay.loadMore(PAGINATION, () => {
      this.loadMore();
    });
  };

  loadAll = () => {
    this.setState({ loading: true });
    this.loadMore();
  };

  render() {
    const {
      relay,
      query: { events },
    } = this.props;
    const { loading } = this.state;
    return (
      <div style={{ position: 'relative' }}>
        {relay.hasMore() && !loading ? (
          <Button
            style={{
              position: 'absolute',
              marginLeft: '-115px',
              left: '50%',
              top: '85%',
              zIndex: '1500',
            }}
            onClick={this.loadAll}>
            <FormattedMessage id="map-load-all-events" />
          </Button>
        ) : null}
        {/* $FlowFixMe */}
        <LeafletMap loading={loading} markers={events} defaultMapOptions={{ zoom: 12 }} />
      </div>
    );
  }
}

export default createPaginationContainer(
  EventMap,
  {
    query: graphql`
      fragment EventMap_query on Query
        @argumentDefinitions(
          count: { type: "Int!" }
          cursor: { type: "String" }
          theme: { type: "ID" }
          project: { type: "ID" }
          search: { type: "String" }
          userType: { type: "ID" }
          isFuture: { type: "Boolean" }
        ) {
        events(
          first: $count
          after: $cursor
          theme: $theme
          project: $project
          search: $search
          userType: $userType
          isFuture: $isFuture
        ) @connection(key: "EventMap_events", filters: []) {
          totalCount
          pageInfo {
            hasNextPage
            endCursor
            hasPreviousPage
            startCursor
          }
          edges {
            node {
              id
              lat
              lng
              url
              fullAddress
              startAt
              endAt
              title
              author {
                username
                url
                media {
                  url
                }
              }
            }
          }
        }
      }
    `,
  },
  {
    direction: 'forward',
    getConnectionFromProps(props: Props) {
      return props.query && props.query.events;
    },
    getFragmentVariables(prevVars) {
      return {
        ...prevVars,
      };
    },
    getVariables(props: Props, { count, cursor }, fragmentVariables) {
      return {
        ...fragmentVariables,
        count,
        cursor,
      };
    },
    query: graphql`
      query EventMapPaginatedQuery(
        $cursor: String
        $count: Int
        $theme: ID
        $project: ID
        $search: String
        $userType: ID
        $isFuture: Boolean
      ) {
        ...EventMap_query
          @arguments(
            cursor: $cursor
            count: $count
            theme: $theme
            project: $project
            search: $search
            userType: $userType
            isFuture: $isFuture
          )
      }
    `,
  },
);
