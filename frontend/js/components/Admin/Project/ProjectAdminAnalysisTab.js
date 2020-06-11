// @flow
import * as React from 'react';
import isEqual from 'lodash/isEqual';
import { graphql, usePreloadedQuery, useQuery } from 'relay-hooks';
import ReactPlaceholder from 'react-placeholder';
import type { ResultPreloadQuery, Query } from '~/types';
import type {
  ProjectAdminAnalysisTabQueryResponse,
  ProjectAdminAnalysisTabQueryVariables,
} from '~relay/ProjectAdminAnalysisTabQuery.graphql';
import type { ProjectAdminPageParameters } from '~/components/Admin/Project/ProjectAdminPage.reducer';
import ProjectAdminAnalysis, {
  PROJECT_ADMIN_PROPOSAL_PAGINATION,
} from '~/components/Admin/Project/ProjectAdminAnalysis';
import { useProjectAdminProposalsContext } from '~/components/Admin/Project/ProjectAdminPage.context';
import PickableList from '~ui/List/PickableList';
import AnalysisPlaceholderDashboard, {
  TYPE_DASHBOARD,
} from '~/components/Analysis/AnalysisPlaceholderdDashboard/AnalysisPlaceholderDashboard';

type Props = {|
  +projectId: string,
  +dataPrefetch: ResultPreloadQuery,
|};

type PropsQuery = {|
  ...Query,
  props: ProjectAdminAnalysisTabQueryResponse,
|};

const createQueryVariables = (
  projectId: string,
  parameters: ProjectAdminPageParameters,
): ProjectAdminAnalysisTabQueryVariables => ({
  projectId,
  count: PROJECT_ADMIN_PROPOSAL_PAGINATION,
  cursor: null,
  orderBy: {
    field: 'PUBLISHED_AT',
    direction: parameters.sort === 'newest' ? 'DESC' : 'ASC',
  },
  category: parameters.filters.category === 'ALL' ? null : parameters.filters.category,
  district: parameters.filters.district === 'ALL' ? null : parameters.filters.district,
  term: parameters.filters.term,
  analysts: parameters.filters.analysts.length > 0 ? parameters.filters.analysts : null,
  supervisor: parameters.filters.supervisor,
  decisionMaker: parameters.filters.decisionMaker,
  progressStatus:
    parameters.filters.progressState === 'ALL' ? null : parameters.filters.progressState,
});

export const queryAnalysis = graphql`
  query ProjectAdminAnalysisTabQuery(
    $projectId: ID!
    $count: Int!
    $cursor: String
    $orderBy: ProposalOrder!
    $category: ID
    $district: ID
    $status: ID
    $term: String
    $analysts: [ID!]
    $supervisor: ID
    $decisionMaker: ID
    $progressStatus: ProposalProgressState
  ) {
    defaultUsers: users(first: 20) {
      edges {
        node {
          id
          ...UserSearchDropdownChoice_user
        }
      }
    }
    project: node(id: $projectId) {
      ...ProjectAdminAnalysis_project
        @arguments(
          projectId: $projectId
          count: $count
          cursor: $cursor
          orderBy: $orderBy
          category: $category
          district: $district
          status: $status
          term: $term
          analysts: $analysts
          supervisor: $supervisor
          decisionMaker: $decisionMaker
          progressStatus: $progressStatus
        )
    }
  }
`;

export const initialVariables = {
  count: PROJECT_ADMIN_PROPOSAL_PAGINATION,
  cursor: null,
  orderBy: {
    field: 'PUBLISHED_AT',
    direction: 'DESC',
  },
  category: null,
  district: null,
  term: null,
  analysts: null,
  supervisor: null,
  decisionMaker: null,
  progressStatus: null,
};

const ProjectAdminAnalysisTab = ({ projectId, dataPrefetch }: Props) => {
  const { parameters } = useProjectAdminProposalsContext();
  const { props: dataPreloaded } = usePreloadedQuery(dataPrefetch);
  const queryVariablesWithParameters = createQueryVariables(projectId, parameters);
  const hasFilters: boolean = !isEqual(
    { projectId, ...initialVariables },
    queryVariablesWithParameters,
  );

  const { props: data, error, retry }: PropsQuery = useQuery(
    queryAnalysis,
    queryVariablesWithParameters,
    {
      skip: !hasFilters,
    },
  );

  if (
    (!hasFilters && dataPreloaded && dataPreloaded.project) ||
    (hasFilters && data && data.project)
  ) {
    const project: any = dataPreloaded && !hasFilters ? dataPreloaded.project : data.project;
    const defaultUsers: any =
      dataPreloaded && !hasFilters ? dataPreloaded.defaultUsers : data.defaultUsers;

    return (
      <PickableList.Provider>
        <ProjectAdminAnalysis project={project} defaultUsers={defaultUsers} />
      </PickableList.Provider>
    );
  }

  return (
    <ReactPlaceholder
      ready={false}
      customPlaceholder={
        <AnalysisPlaceholderDashboard
          type={TYPE_DASHBOARD.BO_ANALYSIS}
          hasError={!!error}
          fetchData={retry}
        />
      }
    />
  );
};

export default ProjectAdminAnalysisTab;
