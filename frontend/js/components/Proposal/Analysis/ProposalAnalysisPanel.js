// @flow
import * as React from 'react';
import { createFragmentContainer, graphql } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import styled, { type StyledComponent } from 'styled-components';
import ProposalAnalysisUserRow from './ProposalAnalysisUserRow';
import type { ProposalAnalysisPanel_proposal } from '~relay/ProposalAnalysisPanel_proposal.graphql';
import colors from '~/utils/colors';
import Icon, { ICON_NAME } from '~/components/Ui/Icons/Icon';
import type { State } from '~/types';

const CloseIcon: StyledComponent<{}, {}, HTMLButtonElement> = styled.button`
  position: absolute;
  right: 30px;
  z-index: 1;
  border: none;
  padding: 0;
  background: none;

  :hover {
    cursor: pointer;
  }
`;

const Panel: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  background: ${colors.white};
  margin-left: 30px;
  margin-right: 30px;
`;

const PanelSection: StyledComponent<{ border?: boolean }, {}, HTMLDivElement> = styled.div`
  > p {
    font-size: 20px;
    color: ${colors.secondaryGray};
    margin-bottom: 15px;
  }

  padding-bottom: 15px;
  border-bottom: ${props => props.border && `1px solid ${colors.lightGray}`};
  margin-bottom: 15px;
`;
type Props = {|
  proposal: ProposalAnalysisPanel_proposal,
  onClose: () => void,
  isAnalysing: boolean,
  user?: Object,
|};

export const ProposalAnalysisPanel = ({ proposal, onClose, isAnalysing, user }: Props) => {
  if (!proposal.analysts || !user) return null;
  const analysts = proposal.analysts.filter(analyst => analyst.id !== user.id);
  const userAnalyst = proposal.analysts?.find(analyst => analyst.id === user.id);
  if (userAnalyst) analysts.unshift(userAnalyst);
  const closed = proposal.decision?.state === 'DONE';
  const decisionState =
    proposal.decision?.state === 'IN_PROGRESS'
      ? 'IN_PROGRESS'
      : proposal.decision?.isApproved === false
      ? 'UNFAVOURABLE'
      : proposal.decision?.isApproved
      ? 'FAVOURABLE'
      : undefined;
  return (
    <div>
      {isAnalysing && (
        <CloseIcon onClick={onClose}>
          <Icon name={ICON_NAME.close} size={16} color={colors.secondaryGray} />
        </CloseIcon>
      )}
      <Panel>
        <PanelSection border>
          <FormattedMessage id="panel.analysis.subtitle" tagName="p" />
          <div>
            {analysts?.map((analyst, idx) => {
              const status = proposal.analyses?.find(a => a.updatedBy.id === analyst.id);
              return (
                <div className="mt-10">
                  <ProposalAnalysisUserRow
                    canConsult={!!status}
                    canEdit={proposal.viewerCanAnalyse && !idx}
                    disabled={
                      (proposal.assessment?.state &&
                        proposal.assessment?.state !== 'IN_PROGRESS') ||
                      closed ||
                      status === 'TOO_LATE'
                    }
                    user={analyst}
                    status={status?.state}
                  />
                </div>
              );
            })}
          </div>
        </PanelSection>
        {proposal.supervisor && (
          <PanelSection border>
            <FormattedMessage id="global.review" tagName="p" />
            <div>
              <ProposalAnalysisUserRow
                canConsult={
                  !!proposal.assessment?.state &&
                  (proposal.viewerCanEvaluate || proposal.viewerCanDecide)
                }
                canEdit={proposal.viewerCanEvaluate}
                disabled={closed || proposal.assessment?.state === 'TOO_LATE'}
                user={proposal.supervisor}
                status={proposal.assessment?.state}
              />
            </div>
          </PanelSection>
        )}
        {proposal.decisionMaker && (
          <PanelSection>
            <FormattedMessage id="global.decision" tagName="p" />
            <div>
              <ProposalAnalysisUserRow
                canConsult={!!proposal.decision?.state && proposal.viewerCanDecide}
                canEdit={proposal.viewerCanDecide && !closed}
                user={proposal.decisionMaker}
                status={decisionState}
                decidor
              />
            </div>
          </PanelSection>
        )}
      </Panel>
    </div>
  );
};

const mapStateToProps = (state: State) => ({
  user: state.user.user,
});

export default createFragmentContainer(connect(mapStateToProps)(ProposalAnalysisPanel), {
  proposal: graphql`
    fragment ProposalAnalysisPanel_proposal on Proposal {
      id
      analysts {
        id
        ...ProposalAnalysisUserRow_user
      }
      decisionMaker {
        ...ProposalAnalysisUserRow_user
      }
      supervisor {
        ...ProposalAnalysisUserRow_user
      }
      analyses {
        id
        state
        updatedBy {
          id
        }
      }
      assessment {
        state
        id
        updatedBy {
          id
        }
      }
      decision {
        id
        state
        updatedBy {
          id
        }
        isApproved
      }

      viewerCanDecide
      viewerCanAnalyse
      viewerCanEvaluate
    }
  `,
});
