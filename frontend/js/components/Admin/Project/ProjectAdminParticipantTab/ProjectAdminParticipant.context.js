// @flow
import * as React from 'react';
import {
  type Action,
  createReducer,
  type ProjectAdminParticipantState,
  type SortValues,
} from './ProjectAdminParticipant.reducer';
import type { Filters, ProjectAdminParticipantParameters } from './ProjectAdminParticipant.reducer';

export type ProjectAdminParticipantStatus = 'ready' | 'loading';

type ProviderProps = {|
  +children: React.Node,
|};

export type Context = {|
  +status: ProjectAdminParticipantStatus,
  +parameters: ProjectAdminParticipantParameters,
  +dispatch: Action => void,
|};

const DEFAULT_STATUS: ProjectAdminParticipantStatus = 'ready';

const DEFAULT_SORT: SortValues = 'newest';

export const DEFAULT_FILTERS: Filters = {
  step: 'ALL',
  type: 'ALL',
  term: null,
};

export const ProjectAdminPageContext = React.createContext<Context>({
  status: DEFAULT_STATUS,
  parameters: {
    sort: DEFAULT_SORT,
    filters: DEFAULT_FILTERS,
    filtersOrdered: [],
  },
  dispatch: () => {},
});

export const useProjectAdminParticipantsContext = (): Context => {
  const context = React.useContext(ProjectAdminPageContext);
  if (!context) {
    throw new Error(
      `You can't use the ProjectAdminPageContext outside a ProjectAdminPageProvider component.`,
    );
  }
  return context;
};

export const ProjectAdminParticipantsProvider = ({ children }: ProviderProps) => {
  const [state, dispatch] = React.useReducer<ProjectAdminParticipantState, Action>(createReducer, {
    status: DEFAULT_STATUS,
    sort: DEFAULT_SORT,
    filters: DEFAULT_FILTERS,
    filtersOrdered: [],
  });

  const context = React.useMemo(
    () => ({
      status: state.status,
      parameters: {
        sort: state.sort,
        filters: state.filters,
        filtersOrdered: state.filtersOrdered,
      },
      dispatch,
    }),
    [state],
  );

  return (
    <ProjectAdminPageContext.Provider value={context}>{children}</ProjectAdminPageContext.Provider>
  );
};
