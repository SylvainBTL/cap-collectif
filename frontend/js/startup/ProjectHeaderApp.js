// @flow
import * as React from 'react';
import { QueryRenderer, graphql } from 'react-relay';
import environment, { graphqlError } from '../createRelayEnvironment';
import Providers from './Providers';
import type { ProjectHeaderAppQueryResponse } from '~relay/ProjectHeaderAppQuery.graphql';
import type { Uuid } from '../types';
import ProjectHeader from '~/components/Project/ProjectHeader';
import Skeleton from '~ds/Skeleton';
import ProjectHeaderPlaceholder from '~/components/Project/ProjectHeader-Placeholder';

const query = graphql`
  query ProjectHeaderAppQuery($projectId: ID!, $count: Int, $cursor: String) {
    project: node(id: $projectId) {
      ...ProjectHeader_project @arguments(count: $count, cursor: $cursor)
    }
  }
`;
export type Props = {|
  +projectId: Uuid,
  +isConsultation?: boolean,
|};

export const ProjectHeaderQueryRenderer = ({ projectId, isConsultation }: Props) => {
  document.getElementsByTagName('html')[0].style.fontSize = '14px';

  return (
    <Providers resetCSS={false} designSystem>
      <QueryRenderer
        fetchPolicy="store-and-network"
        variables={{
          projectId,
          count: 10,
          cursor: null,
        }}
        environment={environment}
        query={query}
        render={({
          error,
          props,
          retry,
        }: {
          ...ReactRelayReadyState,
          props: ?ProjectHeaderAppQueryResponse,
        }) => {
          if (error) {
            return graphqlError;
          }
          if (props && !props.project) {
            console.error('Could not load the project');
            return null;
          }

          return (
            <Skeleton
              isLoaded={!!(props && props.project)}
              placeholder={<ProjectHeaderPlaceholder fetchData={retry} hasError={!!error} />}>
              {!!(props && props.project) && (
                <ProjectHeader project={props.project} isConsultation={isConsultation} />
              )}
            </Skeleton>
          );
        }}
      />
    </Providers>
  );
};

export default ({ projectId, isConsultation }: Props) => (
  <Providers>
    <ProjectHeaderQueryRenderer projectId={projectId} isConsultation={isConsultation} />
  </Providers>
);
