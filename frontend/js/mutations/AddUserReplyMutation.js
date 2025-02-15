// @flow
import { graphql } from 'react-relay';
// eslint-disable-next-line import/no-unresolved
import type {RecordSourceSelectorProxy} from "relay-runtime/store/RelayStoreTypes";
import { ConnectionHandler } from "relay-runtime";
import environment from '../createRelayEnvironment';
import commitMutation from './commitMutation';
import type {
  AddUserReplyMutationVariables,
  AddUserReplyMutationResponse,
} from '~relay/AddUserReplyMutation.graphql';

const mutation = graphql`
  mutation AddUserReplyMutation($input: AddUserReplyInput!, $isAuthenticated: Boolean!) {
    addUserReply(input: $input) {
      reply {
        id
        ...ReplyForm_reply
        ...ReplyLink_reply
        ...UnpublishedLabel_publishable
        questionnaire {
          id
          ...ReplyCreateFormWrapper_questionnaire @arguments(isAuthenticated: $isAuthenticated)
        }
      }
      questionnaire {
        ...ReplyForm_questionnaire @arguments(isAuthenticated: $isAuthenticated)
      }
      errorCode
    }
  }
`;

const commit = (variables: AddUserReplyMutationVariables): Promise<AddUserReplyMutationResponse> =>
  commitMutation(environment, {
    mutation,
    variables,
    updater: (store: RecordSourceSelectorProxy) => {
      const payload = store.getRootField('addUserReply');
      if (!payload) return;

      const reply = payload.getLinkedRecord('reply');
      if (!reply) return;

      const questionnaire = store.get(variables.input.questionnaireId);
      if (!questionnaire) return;

      const userRepliesConnection = ConnectionHandler.getConnection(questionnaire, 'UserReplies_userReplies');
      if (!userRepliesConnection) return;

      const userRepliesTotalCount = parseInt(userRepliesConnection.getValue('totalCount'), 10);
      userRepliesConnection.setValue(userRepliesTotalCount + 1, 'totalCount');

      const edge = ConnectionHandler.createEdge(store, userRepliesConnection, reply, 'ReplyEdge');
      if (!edge) return;

      ConnectionHandler.insertEdgeAfter(userRepliesConnection, edge);
    }
  });

export default { commit };
