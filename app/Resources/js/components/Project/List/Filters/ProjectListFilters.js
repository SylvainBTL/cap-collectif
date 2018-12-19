// @flow
import React from 'react';
import { Col } from 'react-bootstrap';
import { connect, type MapStateToProps } from 'react-redux';
import { reduxForm, formValueSelector, type FormProps } from 'redux-form';
import { type IntlShape } from 'react-intl';

import type { GlobalState, Dispatch } from '../../../../types';
import ProjectsListFilterTypes from './ProjectListFilterTypes';
import ProjectsListFilterAuthors from './ProjectListFilterAuthors';
import ProjectsListFilterThemes from './ProjectListFilterThemes';
import ProjectsListFilterStatus from './ProjectListFilterStatus';
import type { ProjectType, ProjectAuthor, ProjectTheme } from './ProjectListFiltersContainer';

type Props = FormProps & {
  author: ?string,
  dispatch: Dispatch,
  features: { themes: boolean },
  intl: IntlShape,
  projectTypes: ProjectType[],
  projectAuthors: ProjectAuthor[],
  theme: ?string,
  themes: ProjectTheme[],
  type: ?string,
};

class ProjectListFilters extends React.Component<Props> {
  renderTypeFilter() {
    const { projectTypes } = this.props;
    return <ProjectsListFilterTypes projectTypes={projectTypes} />;
  }

  renderAuthorsFilter() {
    const { intl, projectAuthors, author } = this.props;
    return <ProjectsListFilterAuthors authors={projectAuthors} intl={intl} author={author} />;
  }

  renderThemeFilter() {
    const { features, themes, theme, intl } = this.props;
    if (features.themes) {
      return <ProjectsListFilterThemes themes={themes} intl={intl} theme={theme} />;
    }
  }

  renderThemeStatus() {
    const { features, theme, intl } = this.props;
    if (features.themes) {
      return <ProjectsListFilterStatus intl={intl} status={theme} />;
    }
  }

  render() {
    const filters = [];
    filters.push(this.renderTypeFilter());
    filters.push(this.renderThemeFilter());
    filters.push(this.renderAuthorsFilter());
    filters.push(this.renderThemeStatus());

    if (filters.filter(Boolean).length > 0) {
      return (
        <form>
          {filters.map((filter, index) => (
            <Col key={index} className="mt-5">
              <div>{filter}</div>
            </Col>
          ))}
        </form>
      );
    }
    return null;
  }
}

const formName = 'ProjectListFilters';
export const selector = formValueSelector(formName);

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState) => ({
  author: selector(state, 'author'),
  theme: selector(state, 'theme'),
  type: selector(state, 'type'),
  status: selector(state, 'status'),
});

const form = reduxForm({
  form: formName,
  destroyOnUnmount: false,
})(ProjectListFilters);

export default connect(mapStateToProps)(form);
