// @flow
import React, { Component } from 'react';
import { graphql, QueryRenderer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import styled from 'styled-components';
import environment, { graphqlError } from '../../createRelayEnvironment';
import type { SectionContainerQueryResponse } from '~relay/SectionContainerQuery.graphql';
import Loader from '../Ui/FeedbacksIndicators/Loader';
import WYSIWYGRender from '../Form/WYSIWYGRender';

export type Props = {|
  body: string,
  title: string,
  teaser: string,
  metricsToDisplayBasics: boolean,
  metricsToDisplayEvents: boolean,
  metricsToDisplayProjects: boolean,
|};

const MetricsRow = styled.div`
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
`;

const MetricsBox = styled.div.attrs({
  className: 'col-xs-6 col-md-4 col-lg-2',
})`
  background-color: ${color => color.color};
  padding: 20px;
  box-shadow: 1px 2px 0 #0808081a;
  border-radius: 3px;
  min-width: 225px;
  margin-bottom: 15px;
  margin-left: 10px;
`;

export class SectionContainer extends Component<Props> {
  render() {
    const {
      body,
      title,
      teaser,
      metricsToDisplayBasics,
      metricsToDisplayEvents,
      metricsToDisplayProjects,
    } = this.props;

    const metricsSection = document.getElementById('metrics');
    const sectionBgColor = window
      .getComputedStyle(metricsSection, null)
      .getPropertyValue('background-color');
    const colorToDisplay = sectionBgColor === 'rgb(246, 246, 246)' ? 'white' : '#F6F6F6';

    return (
      <div className="row">
        <h2 className="h2">{title}</h2>
        <p className="block">{teaser}</p>
        <p className="block">
          <WYSIWYGRender value={body} />
        </p>
        <QueryRenderer
          variables={{}}
          environment={environment}
          query={graphql`
            query SectionContainerQuery {
              votes: votes {
                totalCount
              }
              contributions: allContributions
              contributors: allContributors {
                totalCount
              }
              events {
                totalCount
              }
              projects(onlyPublic: true) {
                totalCount
              }
            }
          `}
          render={({ error, props }: { props?: ?SectionContainerQueryResponse, ...ReactRelayReadyState }) => {
            if (error) {
              console.log(error); // eslint-disable-line no-console
              return graphqlError;
            }
            if (props) {
              if (
                props.votes &&
                props.contributors &&
                props.events &&
                props.projects &&
                props.contributions !== null
              ) {
                return (
                  <MetricsRow className="row">
                    {metricsToDisplayBasics && (
                      <React.Fragment>
                        <MetricsBox color={colorToDisplay}>
                          <span className="metrics-number">
                            <i className="cap-file-1" />
                            {props.contributions}
                          </span>
                          <p>
                            <FormattedMessage id="capco.section.metrics.contributions" />
                          </p>
                        </MetricsBox>
                        <MetricsBox color={colorToDisplay}>
                          <span className="metrics-number">
                            <i className="cap-user-2" />
                            {props.contributors.totalCount}
                          </span>
                          <p>
                            <FormattedMessage id="capco.section.metrics.participants" />
                          </p>
                        </MetricsBox>
                      </React.Fragment>
                    )}
                    {metricsToDisplayBasics && props.votes && props.votes.totalCount > 0 && (
                      <MetricsBox color={colorToDisplay}>
                        <span className="metrics-number">
                          <i className="cap-hand-like-2" />
                          {props.votes.totalCount}
                        </span>
                        <p>
                          <FormattedMessage id="capco.section.metrics.votes" />
                        </p>
                      </MetricsBox>
                    )}
                    {metricsToDisplayEvents && props.events && props.events.totalCount > 0 && (
                      <MetricsBox color={colorToDisplay}>
                        <span className="metrics-number">
                          <i className="cap-calendar-1" />
                          {props.events.totalCount}
                        </span>
                        <p>
                          <FormattedMessage id="capco.section.metrics.events" />
                        </p>
                      </MetricsBox>
                    )}
                    {metricsToDisplayProjects && (
                      <MetricsBox color={colorToDisplay}>
                        <span className="metrics-number">
                          <i className="cap-folder-2" />
                          {props.projects.totalCount}
                        </span>
                        <p>
                          <FormattedMessage id="capco.section.metrics.projects" />
                        </p>
                      </MetricsBox>
                    )}
                  </MetricsRow>
                );
              }
              return graphqlError;
            }
            return <Loader />;
          }}
        />
      </div>
    );
  }
}

export default SectionContainer;
