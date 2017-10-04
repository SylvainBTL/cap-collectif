// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { FormattedMessage } from 'react-intl';
import { createFragmentContainer, graphql } from 'react-relay';
import { Button, Row, Col, ListGroup, ListGroupItem } from 'react-bootstrap';
import type { GroupAdminUsers_group } from './__generated__/GroupAdminUsers_group.graphql';
import DeleteUserInGroupMutation from '../../../mutations/DeleteUserInGroupMutation';
import GroupAdminModalAddUsers from './GroupAdminModalAddUsers';
import DeleteModal from '../../Modal/DeleteModal';

type Props = { group: GroupAdminUsers_group };
type State = { showAddUsersModal: boolean, showRemoveUserModal: ?number };

const onDelete = (userId: string, groupId: string) => {
  return DeleteUserInGroupMutation.commit({
    input: {
      userId,
      groupId,
    },
  }).then(location.reload());
};

export class GroupAdminUsers extends React.Component<Props, State> {
  state = {
    showAddUsersModal: false,
    showRemoveUserModal: null,
  };

  openCreateModal = () => {
    this.setState({ showAddUsersModal: true });
  };

  handleClose = () => {
    this.setState({ showAddUsersModal: false });
  };

  cancelCloseRemoveUserModal = () => {
    this.setState({ showRemoveUserModal: null });
  };

  render() {
    const { group } = this.props;
    const { showAddUsersModal, showRemoveUserModal } = this.state;

    return (
      <div className="box box-primary container">
        <div className="box-header  pl-0">
          <h4 className="box-title">
            <FormattedMessage id="group.admin.users" />
          </h4>
        </div>
        <Button
          className="mt-5 mb-15"
          bsStyle="success"
          href="#"
          onClick={() => this.openCreateModal()}>
          <i className="fa fa-plus-circle" /> <FormattedMessage id="group.admin.add_users" />
        </Button>
        <GroupAdminModalAddUsers
          show={showAddUsersModal}
          onClose={this.handleClose}
          group={group}
        />
        {group.userGroups.length ? (
          <ListGroup>
            {group.userGroups.map((userGroup, index) => (
              <ListGroupItem key={index}>
                <DeleteModal
                  closeDeleteModal={this.cancelCloseRemoveUserModal}
                  showDeleteModal={index === showRemoveUserModal}
                  deleteElement={() => {
                    onDelete(userGroup.user.id, group.id);
                  }}
                  deleteModalTitle={'group.admin.modal.delete.title'}
                  deleteModalContent={'group.admin.modal.delete.content'}
                />
                <Row>
                  <Col xs={3}>
                    {userGroup.user.media ? (
                      <img
                        className="img-circle mr-15"
                        src={userGroup.user.media.url}
                        alt={userGroup.user.displayName}
                      />
                    ) : (
                      <img
                        className="img-circle mr-15"
                        src="/bundles/sonatauser/default_avatar.png"
                        alt={userGroup.user.displayName}
                      />
                    )}
                    {userGroup.user.displayName}
                  </Col>
                  <Col xs={4}>
                    <p className="mt-10">{userGroup.user.email}</p>
                  </Col>
                  <Col xs={4} className="pull-right">
                    <Button
                      className="pull-right mt-5"
                      bsStyle="danger"
                      href="#"
                      onClick={() => {
                        this.setState({ showRemoveUserModal: index });
                      }}>
                      <i className="fa fa-trash" /> <FormattedMessage id="global.delete" />
                    </Button>
                  </Col>
                </Row>
              </ListGroupItem>
            ))}
          </ListGroup>
        ) : (
          <div style={{ padding: '10px' }}>
            <FormattedMessage id="group.admin.no_users" />
          </div>
        )}
      </div>
    );
  }
}

const mapStateToProps = () => {
  return {};
};

const container = connect(mapStateToProps)(GroupAdminUsers);

export default createFragmentContainer(
  container,
  graphql`
    fragment GroupAdminUsers_group on Group {
      id
      title
      userGroups {
        id
        user {
          id
          displayName
          email
          media {
            url
          }
        }
      }
    }
  `,
);
