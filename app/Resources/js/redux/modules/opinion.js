// @flow
import Fetcher, { json } from '../../services/Fetcher';
import FluxDispatcher from '../../dispatchers/AppDispatcher';
import { takeEvery } from 'redux-saga';
import { call, put } from 'redux-saga/effects';
import { find } from 'lodash';
import { UPDATE_OPINION_SUCCESS, UPDATE_OPINION_FAILURE } from '../../constants/OpinionConstants';

const VOTES_PREVIEW_COUNT = 8;

const OPINION_VOTE_SUCCEEDED = 'opinion/OPINION_VOTE_SUCCEEDED';
const VERSION_VOTE_SUCCEEDED = 'opinion/VERSION_VOTE_SUCCEEDED';
const DELETE_OPINION_VOTE_SUCCEEDED = 'opinion/DELETE_OPINION_VOTE_SUCCEEDED';
const DELETE_VERSION_VOTE_SUCCEEDED = 'opinion/DELETE_VERSION_VOTE_SUCCEEDED';

const OPINION_VOTES_FETCH_REQUESTED = 'opinion/OPINION_VOTES_FETCH_REQUESTED';
const OPINION_VOTES_FETCH_SUCCEEDED = 'opinion/OPINION_VOTES_FETCH_SUCCEEDED';
const OPINION_VOTES_FETCH_FAILED = 'opinion/OPINION_VOTES_FETCH_FAILED';

type VoteValue = -1 | 0 | 1;
type Action =
  { type: string, payload: Object }
;
type State = {
  currentOpinionId: ?number,
  currentVersionId: ?number,
  opinionsById: Object,
  versionsById: Object
};
type Dispatch = (action: Action) => void;

const initialState = {
  currentOpinionId: null,
  opinionsById: {},
  currentVersionId: null,
  versionsById: {},
};

export function* fetchAllOpinionVotes(action: Action): Generator<*, *, *> {
  try {
    let hasMore = true;
    let iterationCount = 0;
    const votesPerIteration = 30;
    const { opinionId, versionId } = action.payload;
    while (hasMore) {
      const votesUrl = versionId
        ? `/opinions/${opinionId}/versions/${versionId}/votes?offset=${iterationCount * votesPerIteration}&limit=${votesPerIteration}`
        : `/opinions/${opinionId}/votes?offset=${iterationCount * votesPerIteration}&limit=${votesPerIteration}`;
      const result: Object = yield call(
        Fetcher.get,
        votesUrl
      );
      hasMore = result.hasMore;
      iterationCount++;
      yield put({ type: OPINION_VOTES_FETCH_SUCCEEDED, payload: { votes: result.votes, opinionId } });
    }
  } catch (e) {
    yield put({ type: OPINION_VOTES_FETCH_FAILED, payload: { error: e } });
  }
}

export function* saga(): Generator<*, *, *> {
  yield* takeEvery(OPINION_VOTES_FETCH_REQUESTED, fetchAllOpinionVotes);
}

export const fetchOpinionVotes = (opinionId: number, versionId: number): Action => ({
  type: OPINION_VOTES_FETCH_REQUESTED,
  payload: {
    opinionId,
    versionId,
  },
});

const versionVoteSuccess = (versionId: number, vote): Action => ({
  type: VERSION_VOTE_SUCCEEDED,
  payload: {
    versionId,
    vote,
  },
});

const opinionVoteSuccess = (opinionId: number, vote): Action => ({
  type: OPINION_VOTE_SUCCEEDED,
  payload: {
    opinionId,
    vote,
  },
});

const deleteOpinionVoteSuccess = (opinionId: number, vote): Action => ({
  type: DELETE_OPINION_VOTE_SUCCEEDED,
  payload: {
    opinionId,
    vote,
  },
});

const deleteVersionVoteSuccess = (versionId: number, vote): Action => ({
  type: DELETE_VERSION_VOTE_SUCCEEDED,
  payload: {
    versionId,
    vote,
  },
});

const deleteVote = (opinion: number, parent: ?number, dispatch: Dispatch) => {
  const url = parent ? `/opinions/${parent}/versions/${opinion}/votes` : `/opinions/${opinion}/votes`;
  return Fetcher
    .delete(url)
    .then(json)
    .then(data => {
      if (parent) {
        dispatch(deleteVersionVoteSuccess(opinion, data));
      } else {
        dispatch(deleteOpinionVoteSuccess(opinion, data));
      }
      FluxDispatcher.dispatch({
        actionType: UPDATE_OPINION_SUCCESS,
        message: 'opinion.request.delete_vote.success',
      });
    })
    .catch(() => {
      FluxDispatcher.dispatch({
        actionType: UPDATE_OPINION_FAILURE,
        message: 'opinion.request.failure',
      });
    });
};

const vote = (value: VoteValue, opinion: number, parent: ?number, dispatch: Dispatch) => {
  const url = parent ? `/opinions/${parent}/versions/${opinion}/votes` : `/opinions/${opinion}/votes`;
  return Fetcher
    .put(url, value)
    .then(json)
    .then(newVote => {
      if (parent) {
        dispatch(versionVoteSuccess(opinion, newVote));
      } else {
        dispatch(opinionVoteSuccess(opinion, newVote));
      }
      FluxDispatcher.dispatch({
        actionType: UPDATE_OPINION_SUCCESS,
        message: 'opinion.request.create_vote.success',
      });
    })
    .catch(() => {
      FluxDispatcher.dispatch({
        actionType: UPDATE_OPINION_FAILURE,
        message: 'opinion.request.failure',
      });
    });
};

export const deleteVoteOpinion = (opinion: number, dispatch: Dispatch) => (
  deleteVote(opinion, null, dispatch))
;

export const deleteVoteVersion = (version: number, opinion: number, dispatch: Dispatch) => (
  deleteVote(version, opinion, dispatch)
);

export const voteOpinion = (value: VoteValue, opinion: number, dispatch: Dispatch) => (
  vote(value, opinion, null, dispatch)
);

export const voteVersion = (value: VoteValue, version: number, opinion: number, dispatch: Dispatch) => (
  vote(value, version, opinion, dispatch)
);

const updateOpinion = (state: State, opinion: Object): State => ({
  ...state,
  opinionsById: { ...state.opinionsById, [opinion.id]: opinion },
});

const updateVersion = (state: State, version: Object): State => ({
  ...state,
  versionsById: { ...state.versionsById, [version.id]: version },
});

export const reducer = (state: State = initialState, action: Action) => {
  const { type, payload } = action;
  switch (type) {
    case OPINION_VOTES_FETCH_SUCCEEDED: {
      let votes = state.opinionsById[payload.opinionId].votes;
      if (votes.length <= VOTES_PREVIEW_COUNT) {
        votes = []; // we remove preview votes
      }
      votes.push(...payload.votes);
      return updateOpinion(state, { ...state.opinionsById[payload.opinionId], votes });
    }
    case OPINION_VOTE_SUCCEEDED: {
      const opinion = state.opinionsById[payload.opinionId];
      const indexOfCurrentUser = opinion.votes.indexOf(find(opinion.votes, (v) => {
        return v.user && v.user.uniqueId === payload.vote.user.uniqueId;
      }));
      return updateOpinion(state, {
        ...opinion,
        ...{
          votes: indexOfCurrentUser === -1 ? [payload.vote, ...opinion.votes] : opinion.votes,
          userHasVote: true,
          votesCount: indexOfCurrentUser === -1 ? opinion.votesCount + 1 : opinion.votesCount,
          user_vote: payload.vote.value,
        },
      });
    }
    case DELETE_OPINION_VOTE_SUCCEEDED: {
      const opinion = state.opinionsById[payload.opinionId];
      const indexToRemove = opinion.votes.indexOf(find(opinion.votes, (v) => {
        return v.user && v.user.uniqueId === payload.vote.user.uniqueId;
      }));
      return updateOpinion(state, {
        ...opinion,
        votes: [...opinion.votes.slice(0, indexToRemove), ...opinion.votes.slice(indexToRemove + 1)],
        user_vote: null,
        userHasVote: false,
        votesCount: opinion.votesCount - 1,
      });
    }
    case VERSION_VOTE_SUCCEEDED: {
      return updateVersion(state, {
        ...state.versionsById[payload.versionId],
        user_vote: payload.vote.value,
        userHasVote: true,
      });
    }
    case DELETE_VERSION_VOTE_SUCCEEDED: {
      return updateVersion(state, {
        ...state.versionsById[payload.versionId],
        user_vote: null,
        userHasVote: false,
      });
    }
    case OPINION_VOTES_FETCH_FAILED: {
      console.log(OPINION_VOTES_FETCH_FAILED, payload.error); // eslint-disable-line no-console
      return state;
    }
    default:
      return state;
  }
};
