import AppDispatcher from '../dispatchers/AppDispatcher';
import Fetcher from '../services/Fetcher';
import * as Actions from '../constants/SynthesisElementActionsConstants';
import {NAV_DEPTH} from '../constants/SynthesisElementConstants';
import {DISMISS_MESSAGE} from '../constants/MessageConstants';

const idOf = (val) => {
  if (val === 'root') {
    return null;
  }
  if (val !== null && typeof val === 'object') {
    if (val.id === 'root') {
      return null;
    }
    return val.id;
  }
  return val;
};

const updateElementFromData = (synthesis, element, data, successMessage = 'common.success.update_success', errorMessage = 'common.errors.update_error') => {
  return Fetcher
    .put(`/syntheses/${synthesis}/elements/${element}`, data)
    .then(() => {
      AppDispatcher.dispatch({
        actionType: Actions.UPDATE_ELEMENT_SUCCESS,
        message: successMessage,
      });
      return true;
    })
    .catch(() => {
      AppDispatcher.dispatch({
        actionType: Actions.UPDATE_ELEMENT_FAILURE,
        message: errorMessage,
      });
      return false;
    });
};

const createElementFromData = (synthesis, data, successMessage = 'common.success.update_success', errorMessage = 'common.errors.update_error') => {
  return Fetcher
    .post(`/syntheses/${synthesis}/elements`, data)
    .then((response) => {
      response.json().then((element) => {
        AppDispatcher.dispatch({
          actionType: Actions.CREATE_ELEMENT_SUCCESS,
          element: element,
          message: successMessage,
        });
        return true;
      });
      return true;
    })
    .catch(() => {
      AppDispatcher.dispatch({
        actionType: Actions.CREATE_ELEMENT_FAILURE,
        message: errorMessage,
      });
      return false;
    });
};

const fetchElementById = (synthesis, element) => {
  return Fetcher
    .get(`/syntheses/${synthesis}/elements/${element}`)
    .then((data) => {
      AppDispatcher.dispatch({
        actionType: Actions.RECEIVE_ELEMENT_SUCCESS,
        element: data,
      });
      return true;
    })
    .catch(() => {
      AppDispatcher.dispatch({
        actionType: Actions.RECEIVE_ELEMENT_FAILURE,
      });
      return false;
    });
};

export default {

  create: (synthesis, data) => {
    AppDispatcher.dispatch({
      actionType: Actions.CREATE_ELEMENT,
      element: data,
    });
    if (data.parent) {
      data.parent = idOf(data.parent);
    }
    createElementFromData(synthesis, data, 'common.success.create_success', 'common.errors.create_error');
  },

  loadElementFromServer: (synthesis, element) => {
    AppDispatcher.dispatch({
      actionType: Actions.RECEIVE_ELEMENT,
      elementId: element,
    });
    fetchElementById(synthesis, element);
  },

  loadElementsFromServer: (synthesis, type, offset, limit) => {
    Fetcher
      .get(`/syntheses/${synthesis}/elements?type=${type}&offset=${offset}&limit=${limit}`)
      .then((data) => {
        data.actionType = Actions.RECEIVE_ELEMENTS_SUCCESS;
        data.type = type;
        AppDispatcher.dispatch(data);
        return true;
      });
  },

  loadElementsTreeFromServer: (synthesis, type, parent = null) => {
    AppDispatcher.dispatch({
      actionType: Actions.RECEIVE_ELEMENTS,
      type: type + 'Tree',
    });
    let url = `/syntheses/${synthesis}/elements/tree?type=${type}&depth=${NAV_DEPTH}`;
    url += parent ? `&parent=${parent}` : '';
    Fetcher
      .get(url)
      .then((data) => {
        AppDispatcher.dispatch({
          actionType: Actions.RECEIVE_ELEMENTS_SUCCESS,
          type: type + 'Tree',
          elements: data,
          parent: parent,
        });
        return true;
      })
      .catch((err) => {
        console.error(err);
        AppDispatcher.dispatch({
          actionType: Actions.RECEIVE_ELEMENTS_FAILURE,
          type: type + 'Tree',
        });
        return false;
      });
  },

  loadElementsCountFromServer: (synthesis, type) => {
    Fetcher
      .get(`/syntheses/${synthesis}/elements/count?type=${type}`)
      .then((data) => {
        AppDispatcher.dispatch({
          actionType: Actions.RECEIVE_COUNT,
          type: type,
          count: data.count,
        });
        return true;
      });
  },

  update: (synthesis, element, data) => {
    if (data.archived || data.published) {
      AppDispatcher.dispatch({
        actionType: Actions.ARCHIVE_ELEMENT,
        archived: data.archived,
        published: data.published,
        elementId: element,
      });
    }
    if (data.parent) {
      AppDispatcher.dispatch({
        actionType: Actions.MOVE_ELEMENT,
        parent: data.parent,
        elementId: element,
      });
      data.parent = idOf(data.parent);
    }
    if (data.notation) {
      AppDispatcher.dispatch({
        actionType: Actions.NOTE_ELEMENT,
        notation: data.notation,
        elementId: element,
      });
    }
    if (data.title) {
      AppDispatcher.dispatch({
        actionType: Actions.NAME_ELEMENT,
        title: data.title,
        elementId: element,
      });
    }
    if (data.description) {
      AppDispatcher.dispatch({
        actionType: Actions.DESCRIBE_ELEMENT,
        description: data.description,
        elementId: element,
      });
    }
    if (data.comment) {
      AppDispatcher.dispatch({
        actionType: Actions.COMMENT_ELEMENT,
        comment: data.comment,
        elementId: element,
      });
    }
    if (data.division) {
      data.division.elements.forEach((el, index) => {
        data.division.elements[index].parent = idOf(el.parent);
      });
      AppDispatcher.dispatch({
        actionType: Actions.DIVIDE_ELEMENT,
        division: data.division,
        elementId: element,
      });
    }
    updateElementFromData(synthesis, element, data, 'common.success.archive_success', 'common.errors.archive_error');
  },

  expandTreeItem(type, elementId, expanded) {
    AppDispatcher.dispatch({
      actionType: Actions.EXPAND_TREE_ITEM,
      elementId: elementId,
      expanded: expanded,
      type: type,
    });
  },

  selectNavItem(elementId) {
    AppDispatcher.dispatch({
      actionType: Actions.SELECT_NAV_ITEM,
      elementId: elementId,
    });
  },

  dismissMessage(message, type) {
    AppDispatcher.dispatch({
      actionType: DISMISS_MESSAGE,
      message: message,
      type: type,
    });
  },

};
