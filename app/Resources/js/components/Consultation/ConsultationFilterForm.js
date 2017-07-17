// @flow
import React from 'react';
import { IntlMixin } from 'react-intl';
import { Field, reduxForm } from 'redux-form';
import renderComponent from '../Form/Field';

export const form = 'consultation-filter-form';

export const ConsultationFilterForm = React.createClass({
  mixins: [IntlMixin],

  render() {
    return (
      <form className="form-inline">
        <Field
          id="proposition_type_filter"
          name="proposition_type_filter"
          component={renderComponent}
          type="select"
          disableValidation>
          <option value="opinions">Toutes les propositions</option>
          <option value="versions">Tous les amendements</option>
        </Field>
        {' '}
        <Field
          id="proposition_type_order"
          name="proposition_type_order"
          component={renderComponent}
          type="select"
          disableValidation>
          <option value="">Triée par section</option>
          <option value="recent">Les plus récentes</option>
          <option value="old">Les plus anciennes</option>
          <option value="voted">Les plus votées</option>
          <option value="popular">Les plus favorables</option>
          <option value="unpopular">Les plus défavorables</option>
          <option value="argued">Les plus argumentées</option>
        </Field>
      </form>
    );
  },
});

export default reduxForm({
  form,
})(ConsultationFilterForm);
