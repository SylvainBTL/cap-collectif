// @flow
import React, { useMemo, useState } from 'react';
import { connect } from 'react-redux';
import { loadQuery, RelayEnvironmentProvider } from 'relay-hooks';
import { formValueSelector } from 'redux-form';
import { BrowserRouter as Router, Link, Route, Switch, useLocation } from 'react-router-dom';
import { createFragmentContainer, graphql } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { type ProjectAdminContent_project } from '~relay/ProjectAdminContent_project.graphql';
import environment from '~/createRelayEnvironment';
import type { FeatureToggles, GlobalState } from '~/types';
import ProjectAdminForm from './Form/ProjectAdminForm';
import { Content, Count, Header, NavContainer, NavItem } from './ProjectAdminContent.style';
import ProjectAdminProposalsPage, {
  initialVariables as queryVariableContribution,
  queryProposals,
} from '~/components/Admin/Project/ProjectAdminProposalsPage';
import ProjectAdminAnalysisTab, {
  initialVariables as queryVariableAnalysis,
  queryAnalysis,
} from '~/components/Admin/Project/ProjectAdminAnalysisTab';
import Icon, { ICON_NAME } from '~ui/Icons/Icon';
import { BoxDeprecated, BoxContainer } from './Form/ProjectAdminForm.style';
import { ProjectAdminProposalsProvider } from '~/components/Admin/Project/ProjectAdminPage.context';

type Props = {|
  +features: FeatureToggles,
  +project: ProjectAdminContent_project,
|};

type Links = Array<{|
  title: string,
  count?: number,
  url: string,
  component: any,
|}>;

// TODO replace the WIP placeholder when components are ready
const formatNavbarLinks = (project, features, path, setTitle, dataPrefetchPage) => {
  const links = [];
  const hasCollectStep = project.steps.some(step => step.type === 'CollectStep');
  if (hasCollectStep)
    links.push({
      title: 'global.contribution',
      count: project.proposals.totalCount,
      url: `${path}/proposals`,
      component: () => (
        <ProjectAdminProposalsPage
          projectId={project.id}
          dataPrefetch={dataPrefetchPage.contribution}
        />
      ),
    });
  links.push({
    title: 'capco.section.metrics.participants',
    count: project.contributors.totalCount,
    url: `#`,
    component: () => <p style={{ marginLeft: '45%' }}>WIP</p>,
  });

  if (features.unstable__analysis && project.hasAnalysis)
    links.push({
      title: 'proposal.tabs.evaluation',
      url: `${path}/analysis`,
      count: project.firstAnalysisStep ? project.firstAnalysisStep.proposals.totalCount : undefined,
      component: () => (
        <ProjectAdminAnalysisTab projectId={project.id} dataPrefetch={dataPrefetchPage.analysis} />
      ),
    });
  links.push({
    title: 'global.configuration',
    url: `${path}/edit`,
    component: () => <ProjectAdminForm project={project} onTitleChange={setTitle} />,
  });
  return links;
};

// TODO: change when the page is complete
const basePath = '/admin/alpha/project/';

export const ProjectAdminContent = ({ project, features }: Props) => {
  const location = useLocation();
  const [title, setTitle] = useState(project.title);
  const path = `${basePath}${project._id}`;

  const dataAnalysisPrefetch = loadQuery();
  dataAnalysisPrefetch.next(
    environment,
    queryAnalysis,
    { projectId: project.id, ...queryVariableAnalysis },
    { fetchPolicy: 'store-or-network' },
  );

  const dataContributionPublishedPrefetch = loadQuery();
  dataContributionPublishedPrefetch.next(
    environment,
    queryProposals,
    { projectId: project.id, ...queryVariableContribution },
    { fetchPolicy: 'store-or-network' },
  );

  const dataPrefetchPage = {
    analysis: dataAnalysisPrefetch,
    contribution: dataContributionPublishedPrefetch,
  };

  const links: Links = useMemo(
    () => formatNavbarLinks(project, features, path, setTitle, dataPrefetchPage),
    [project, features, path, setTitle, dataPrefetchPage],
  );

  return (
    <div className="d-flex">
      <Header>
        <div>
          <h1>{title}</h1>
          <a href={project.url} target="_blank" rel="noopener noreferrer">
            <Icon name={ICON_NAME.externalLink} size="14px" />
            <FormattedMessage id="global.preview" />
          </a>
        </div>
        <NavContainer>
          {links.map((link, idx) => (
            <NavItem key={idx} active={location.pathname === link.url}>
              <Link to={link.url}>
                <FormattedMessage id={link.title} />
              </Link>
              {link.count !== undefined && (
                <Count active={location.pathname === link.url}>{link.count}</Count>
              )}
            </NavItem>
          ))}
        </NavContainer>
      </Header>
      <Content>
        <BoxContainer className="box container-fluid" color="#ffc206">
          <BoxDeprecated>
            <FormattedMessage id="message.page.previous.version" />
            <a href={project.adminUrl}>
              <FormattedMessage id="global.consult" /> <i className="cap cap-arrow-66" />
            </a>
          </BoxDeprecated>
        </BoxContainer>

        <Switch>
          {links.map(link => (
            <Route key={link.url} path={link.url}>
              {link.component()}
            </Route>
          ))}
        </Switch>
      </Content>
    </div>
  );
};

const ProjectAdminRouterWrapper = ({
  project,
  features,
  firstCollectStepId,
}: {
  ...Props,
  +firstCollectStepId?: ?string,
}) => (
  <RelayEnvironmentProvider environment={environment}>
    <Router>
      <ProjectAdminProposalsProvider firstCollectStepId={firstCollectStepId}>
        <ProjectAdminContent project={project} features={features} />
      </ProjectAdminProposalsProvider>
    </Router>
  </RelayEnvironmentProvider>
);

const mapStateToProps = (state: GlobalState) => ({
  features: state.default.features,
  title: formValueSelector('projectAdminForm')(state, 'title'),
});

export default createFragmentContainer(connect(mapStateToProps)(ProjectAdminRouterWrapper), {
  project: graphql`
    fragment ProjectAdminContent_project on Project
      @argumentDefinitions(projectId: { type: "ID!" }) {
      _id
      id
      title
      url
      hasAnalysis
      adminUrl
      proposals {
        totalCount
      }
      firstAnalysisStep {
        proposals {
          totalCount
        }
      }
      contributors {
        totalCount
      }
      steps {
        type: __typename
      }
      ...ProjectAdminForm_project
    }
  `,
});
