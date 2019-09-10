// @flow
import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import { QueryRenderer, graphql } from 'react-relay';
import IntlProvider from './IntlProvider';
import environment, { graphqlError } from '../createRelayEnvironment';
import ProposalVoteBasketWidget from '../components/Proposal/Vote/ProposalVoteBasketWidget';
import type {
  ProposalVoteBasketWidgetAppQueryResponse,
  ProposalVoteBasketWidgetAppQueryVariables,
} from '~relay/ProposalVoteBasketWidgetAppQuery.graphql';

type Props = {
  stepId: string,
  votesPageUrl: string,
  image: string,
};

export default (data: Props) => (
  <Provider store={ReactOnRails.getStore('appStore')}>
    <IntlProvider>
      <QueryRenderer
        variables={
          ({
            stepId: data.stepId,
          }: ProposalVoteBasketWidgetAppQueryVariables)
        }
        environment={environment}
        query={graphql`
          query ProposalVoteBasketWidgetAppQuery($stepId: ID!) {
            step: node(id: $stepId) {
              ...ProposalVoteBasketWidget_step
            }
            viewer {
              ...ProposalVoteBasketWidget_viewer @arguments(stepId: $stepId)
            }
          }
        `}
        render={({
          error,
          props,
        }: {
          props: ?ProposalVoteBasketWidgetAppQueryResponse,
          ...ReactRelayReadyState,
        }) => {
          if (error) {
            return graphqlError;
          }
          if (props) {
            if (props.step) {
              return (
                <ProposalVoteBasketWidget
                  step={props.step}
                  viewer={props.viewer}
                  image={data.image}
                  votesPageUrl={data.votesPageUrl}
                />
              );
            }
            return graphqlError;
          }
          return null;
        }}
      />
    </IntlProvider>
  </Provider>
);
