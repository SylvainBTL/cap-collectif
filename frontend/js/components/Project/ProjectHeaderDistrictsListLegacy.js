// @flow
import * as React from 'react';
import { ListGroupItem } from 'react-bootstrap';
import { graphql, useFragment } from 'react-relay';
import { FormattedMessage, useIntl } from 'react-intl';
import styled from 'styled-components';
import { useDisclosure } from '@liinkiing/react-hooks';
import type { StyledComponent } from 'styled-components';
import Modal from '~ds/Modal/Modal';
import colors from '~/styles/modules/colors';
import ProjectHeader from '~ui/Project/ProjectHeaderLegacy';
import Button from '~ds/Button/Button';

import ListGroupFlush from '../Ui/List/ListGroupFlush';
import { type ProjectHeaderDistrictsListLegacy_project$key } from '~relay/ProjectHeaderDistrictsListLegacy_project.graphql';

type Props = {|
  +breakingNumber: number,
  +project: ProjectHeaderDistrictsListLegacy_project$key,
|};

export const DistrictsButton: StyledComponent<
  { archived: boolean },
  {},
  typeof ProjectHeader.Info.Location,
> = styled(ProjectHeader.Info.Location)`
  cursor: pointer;
  vertical-align: baseline;
  color: ${props => (props.archived ? `${colors['neutral-gray']['500']} !important` : null)};
`;

const FRAGMENT = graphql`
  fragment ProjectHeaderDistrictsListLegacy_project on Project {
    districts {
      totalCount
      edges {
        node {
          name
        }
      }
    }
    archived
  }
`;

const ProjectHeaderDistrictsListLegacy = ({ breakingNumber, project }: Props) => {
  const { isOpen, onOpen, onClose } = useDisclosure(false);
  const intl = useIntl();
  const data = useFragment(FRAGMENT, project);
  if (!!data.districts && !!data.districts.edges) {
    if (!!data.districts?.totalCount && data.districts?.totalCount <= breakingNumber) {
      return data.districts?.edges?.map<any>((district: any, key: number) => (
        <ProjectHeader.Info.Location key={key} content={district?.node?.name} />
      ));
    }
    return (
      <>
        <DistrictsButton
          content={
            <>
              {data.districts.edges[0]?.node?.name}{' '}
              <FormattedMessage
                id="and-count-other-areas"
                values={{
                  count: data.districts.totalCount - 1,
                }}
              />
            </>
          }
          onClick={onOpen}
          className="p-0 data-districts__modal-link"
          archived={data.archived}
        />
        <Modal
          show={isOpen}
          onClose={onClose}
          ariaLabel={intl.formatMessage({ id: 'data_district_list' })}>
          <Modal.Header>
            <FormattedMessage
              id="count-area"
              values={{
                count: data.districts?.totalCount,
              }}
            />
          </Modal.Header>
          <Modal.Body>
            <ListGroupFlush>
              {data.districts?.totalCount &&
                data.districts?.totalCount > 0 &&
                data.districts?.edges?.map((district, key) => (
                  <ListGroupItem key={key}>{district?.node?.name}</ListGroupItem>
                ))}
            </ListGroupFlush>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="primary" variantSize="medium" onClick={onClose}>
              <FormattedMessage id="global.close" />
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    );
  }

  return null;
};
export default ProjectHeaderDistrictsListLegacy;
