// @flow
import * as React from 'react';
import { graphql, createPaginationContainer, type RelayPaginationProp } from 'react-relay';
import { ListGroup } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';
import OpinionVersion from './OpinionVersion';
import type { OpinionVersionListViewPaginated_opinion } from './__generated__/OpinionVersionListViewPaginated_opinion.graphql';

type Props = {
  relay: RelayPaginationProp,
  opinion: OpinionVersionListViewPaginated_opinion,
};

class OpinionVersionListViewPaginated extends React.Component<Props> {
  render() {
    const { opinion } = this.props;
    if (!opinion.versions.edges || opinion.versions.edges.length === 0) {
      return (
        <p className="text-center">
          <i className="cap-32 cap-baloon-1" />
          <br />
          <FormattedMessage id="opinion.no_new_version" />
        </p>
      );
    }

    return (
      <ListGroup id="versions-list" className="media-list" style={{ marginTop: '20px' }}>
        {opinion.versions.edges
          .filter(Boolean)
          .map(edge => edge.node)
          .filter(Boolean)
          .map(version => {
            // $FlowFixMe https://github.com/cap-collectif/platform/issues/4973
            return <OpinionVersion key={version.id} version={version} />;
          })}
      </ListGroup>
    );
  }
}

export default createPaginationContainer(
  OpinionVersionListViewPaginated,
  {
    opinion: graphql`
      fragment OpinionVersionListViewPaginated_opinion on Opinion
        @argumentDefinitions(
          isAuthenticated: { type: "Boolean", defaultValue: true }
          count: { type: "Int!" }
          cursor: { type: "String" }
          orderBy: { type: "VersionOrder!", nonNull: true }
        ) {
        id
        versions(first: $count, after: $cursor, orderBy: $orderBy)
          @connection(key: "OpinionVersionListViewPaginated_versions", filters: ["orderBy"]) {
          totalCount
          edges {
            node {
              id
              ...OpinionVersion_version @arguments(isAuthenticated: $isAuthenticated)
            }
          }
          pageInfo {
            hasPreviousPage
            hasNextPage
            startCursor
            endCursor
          }
        }
      }
    `,
  },
  {
    direction: 'forward',
    getConnectionFromProps(props: Props) {
      return props.opinion && props.opinion.versions;
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
        opinionId: props.opinion.id,
      };
    },
    query: graphql`
      query OpinionVersionListViewPaginatedPaginatedQuery(
        $opinionId: ID!
        $isAuthenticated: Boolean!
        $cursor: String
        $orderBy: VersionOrder!
        $count: Int!
      ) {
        opinion: node(id: $opinionId) {
          id
          ...OpinionVersionListViewPaginated_opinion
            @arguments(
              isAuthenticated: $isAuthenticated
              cursor: $cursor
              orderBy: $orderBy
              count: $count
            )
        }
      }
    `,
  },
);
