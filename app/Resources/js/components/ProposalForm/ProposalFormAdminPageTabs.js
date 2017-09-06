// @flow
import React, { Component } from 'react';
import { Tabs, Tab } from 'react-bootstrap';
import { injectIntl } from 'react-intl';
import { createFragmentContainer, graphql } from 'react-relay';
import ProposalFormAdminConfigurationForm from './ProposalFormAdminConfigurationForm';
import type { ProposalFormAdminPageTabs_proposalForm } from './__generated__/ProposalFormAdminPageTabs_proposalForm.graphql';

type DefaultProps = void;
type Props = { proposalForm: ProposalFormAdminPageTabs_proposalForm, intl: Object };
type State = void;

export class ProposalFormAdminPageTabs extends Component<Props, State> {
  static defaultProps: DefaultProps;
  render() {
    const { intl, proposalForm } = this.props;
    return (
      <div>
        <p>
          <strong>Permalien :</strong> <a href={proposalForm.url}>{proposalForm.url}</a>
        </p>
        <Tabs defaultActiveKey={2} id="proposal-form-admin-page-tabs">
          <Tab
            eventKey={1}
            title={intl.formatMessage({ id: 'proposal_form.admin.activity' })}
            disabled
          />
          <Tab eventKey={2} title={intl.formatMessage({ id: 'proposal_form.admin.configuration' })}>
            <ProposalFormAdminConfigurationForm proposalForm={proposalForm} />
          </Tab>
          <Tab
            eventKey={3}
            title={intl.formatMessage({ id: 'proposal_form.admin.notification' })}
          />
          <Tab eventKey={4} title={intl.formatMessage({ id: 'proposal_form.admin.settings' })} />
        </Tabs>
      </div>
    );
  }
}

const container = injectIntl(ProposalFormAdminPageTabs);

export default createFragmentContainer(
  container,
  graphql`
    fragment ProposalFormAdminPageTabs_proposalForm on ProposalForm {
      title
      url
    }
  `,
);
