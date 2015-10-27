import AppDispatcher from '../dispatchers/AppDispatcher';
import Fetcher from '../services/Fetcher';
import {
  RECEIVE_PROPOSAL,
  RECEIVE_PROPOSALS,

  CREATE_PROPOSAL_VOTE,
  CREATE_PROPOSAL_VOTE_SUCCESS,
  CREATE_PROPOSAL_VOTE_FAILURE,

  DELETE_PROPOSAL_VOTE,
  DELETE_PROPOSAL_VOTE_SUCCESS,
  DELETE_PROPOSAL_VOTE_FAILURE,

  RECEIVE_PROPOSAL_COMMENTS,

  CREATE_PROPOSAL_COMMENT,
  CREATE_PROPOSAL_COMMENT_SUCCESS,
  CREATE_PROPOSAL_COMMENT_FAILURE,
} from '../constants/ProposalConstants';

export default {

  load: (form, order = 'last', filters = {}, offset = 0, limit = -1) => {
    let url = `/proposal_forms/${form}/proposals?order=${order}&offset=${offset}&limit=${limit}`;
    for (const filter in filters) {
      if (filters.hasOwnProperty(filter)) {
        url += `&${filter}=${filters[filter]}`;
      }
    }

    Fetcher
      .get(url)
      .then((data) => {
        AppDispatcher.dispatch({
          actionType: RECEIVE_PROPOSALS,
          proposals: data,
        });
        return true;
      });
  },

  getOne: (form, proposal) => {
    Fetcher
      .get(`/proposal_forms/${form}/proposals/${proposal}`)
      .then((data) => {
        AppDispatcher.dispatch({
          actionType: RECEIVE_PROPOSAL,
          proposal: data,
        });
        return true;
      });
  },

  vote: (form, proposal, data) => {
    AppDispatcher.dispatch({
      actionType: CREATE_PROPOSAL_VOTE,
    });
    return Fetcher
    .post(`/proposal_forms/${form}/proposals/${proposal}/votes`, data)
    .then(() => {
      AppDispatcher.dispatch({
        actionType: CREATE_PROPOSAL_VOTE_SUCCESS,
      });
      return true;
    })
    .catch(() => {
      AppDispatcher.dispatch({
        actionType: CREATE_PROPOSAL_VOTE_FAILURE,
      });
    });
  },

  deleteVote: (form, proposal) => {
    AppDispatcher.dispatch({
      actionType: DELETE_PROPOSAL_VOTE,
    });
    return Fetcher
      .delete(`/proposal_forms/${form}/proposals/${proposal}/votes`)
      .then(() => {
        AppDispatcher.dispatch({
          actionType: DELETE_PROPOSAL_VOTE_SUCCESS,
        });
        return true;
      })
      .catch(() => {
        AppDispatcher.dispatch({
          actionType: DELETE_PROPOSAL_VOTE_FAILURE,
        });
      });
  },

  loadComments: (form, proposal, filter, offset = 0, limit = -1) => {
    return Fetcher
      .get(`/proposal_forms/${form}/proposals/${proposal}/comments?filter=${filter}&offset=${offset}&limit=${limit}`)
      .then((data) => {
        AppDispatcher.dispatch({
          actionType: RECEIVE_PROPOSAL_COMMENTS,
          comments: data.comments,
        });
        return true;
      });
  },

  addComment: (form, proposal, data) => {
    AppDispatcher.dispatch({
      actionType: CREATE_PROPOSAL_COMMENT,
    });
    return Fetcher
      .post(`/proposal_forms/${form}/proposals/${proposal}/comments`, data)
      .then(() => {
        AppDispatcher.dispatch({
          actionType: CREATE_PROPOSAL_COMMENT_SUCCESS,
          type: data.type,
        });
        return true;
      })
      .catch(() => {
        AppDispatcher.dispatch({
          actionType: CREATE_PROPOSAL_COMMENT_FAILURE,
        });
      });
  },

};
