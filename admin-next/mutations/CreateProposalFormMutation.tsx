import { graphql } from 'react-relay';
import { RecordSourceSelectorProxy } from 'relay-runtime';
import { environment } from 'utils/relay-environement';
import commitMutation from './commitMutation';
import type {
  CreateProposalFormMutation,
  CreateProposalFormMutationResponse,
  CreateProposalFormMutationVariables,
} from '@relay/CreateProposalFormMutation.graphql';
import {ModalCreateProposalForm_viewer} from "@relay/ModalCreateProposalForm_viewer.graphql";

const mutation = graphql`
    mutation CreateProposalFormMutation($input: CreateProposalFormInput!, $connections: [ID!]!)
    @raw_response_type
    {
        createProposalForm(input: $input) {
            proposalForm @prependNode(connections: $connections, edgeTypeName: "ProposalFormEdge") {
                ...ProposalFormItem_proposalForm
                adminUrl
            }
        }
    }
`;

const commit = (
  variables: CreateProposalFormMutationVariables,
  isAdmin: boolean,
  owner: ModalCreateProposalForm_viewer,
  hasProposalForm: boolean,
): Promise<CreateProposalFormMutationResponse> =>
  commitMutation<CreateProposalFormMutation>(environment, {
    mutation,
    variables,
    optimisticResponse: {
      createProposalForm: {
        proposalForm: {
          id: new Date().toISOString(),
          title: variables.input.title,
          createdAt: new Date().toString(),
          updatedAt: new Date().toString(),
          step: null,
          adminUrl: '',
          owner
        },
      },
    },
    updater: (store: RecordSourceSelectorProxy) => {
      if (!hasProposalForm) return;
      const payload = store.getRootField('createProposalForm');
      if (!payload) return;
      const errorCode = payload.getValue('errorCode');
      if (errorCode) return;

      const rootFields = store.getRoot();
      const viewer = rootFields.getLinkedRecord('viewer');
      if (!viewer) return;
      const proposalForms = viewer.getLinkedRecord('proposalForms', {
        affiliations: isAdmin ? null : ['OWNER'],
      });
      if (!proposalForms) return;

      const proposalFormsTotalCount = parseInt(String(proposalForms.getValue('totalCount')), 10);
      proposalForms.setValue(proposalFormsTotalCount + 1, 'totalCount');
    },
  });

export default { commit };
