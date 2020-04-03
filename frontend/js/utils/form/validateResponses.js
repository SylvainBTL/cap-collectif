// @flow

import type { IntlShape } from 'react-intl';
import type {
  Questions,
  ResponsesInReduxForm,
  ResponsesError,
  ResponsesWarning,
} from '~/components/Form/Form.type';
import stripHtml from '~/utils/stripHtml';
import formatResponses from '~/utils/form/formatResponses';
import type { FormattedResponse } from '~/utils/form/formatResponses';
import { checkOnlyNumbers, checkSiret } from '~/services/Validator';

const getResponseNumber = (value: ?string | ?Array<string>, otherValue: ?string) => {
  if (Array.isArray(value) && value.length > 0) {
    const labelsNumber = value.length;
    const hasOtherValue = otherValue ? 1 : 0;
    return labelsNumber + hasOtherValue;
  }

  return 0;
};

// Error rule order by priority
export const validateResponses = (
  questions: Questions,
  responses: ResponsesInReduxForm,
  className: string,
  intl: IntlShape,
  isDraft: boolean = false,
  availableQuestionIds: Array<string> = [],
): { responses: ResponsesError | ResponsesWarning } => {
  const formattedResponses: Array<FormattedResponse> = formatResponses(questions, responses);

  const responsesError = formattedResponses.map((formattedResponse: FormattedResponse) => {
    const { idQuestion, type, required, validationRule, value, otherValue, hidden } = formattedResponse;

    // required
    if (required &&!isDraft
      && !hidden
    ) {
      // no value
      if (
        !value || // default
        (!value && !otherValue) || // checkbox & radio
        (value && value.length === 0) || // checkbox & radio & ranking & media
        (type === 'editor' && typeof value === 'string' && !stripHtml(value)) // editor
      ) {
        return { idQuestion, value: `${className}.constraints.field_mandatory` };
      }

      if (type === 'siren' && (!value || (typeof value === 'string' && !checkSiret(value)))) {
        return { idQuestion, value: `please-enter-a-siren` };
      }
    }

    if (type === 'number' && value && typeof value === 'string' && !checkOnlyNumbers(value)) {
      return { idQuestion, value: `please-enter-a-number` };
    }

    if (validationRule && ((value && value.length > 0) || otherValue) && !isDraft) {
      const responsesNumber = getResponseNumber(value, otherValue);

      if (
        validationRule.type === 'MIN' &&
        validationRule.number &&
        responsesNumber < validationRule.number
      ) {
        return {
          idQuestion,
          value: intl.formatMessage(
            { id: 'reply.constraints.choices_min' },
            { nb: validationRule.number },
          ),
        };
      }

      if (
        validationRule.type === 'MAX' &&
        validationRule.number &&
        responsesNumber > validationRule.number
      ) {
        return {
          idQuestion,
          value: intl.formatMessage(
            { id: 'reply.constraints.choices_max' },
            { nb: validationRule.number },
          ),
        };
      }

      if (validationRule.type === 'EQUAL' && responsesNumber !== validationRule.number) {
        return {
          idQuestion,
          value: intl.formatMessage(
            { id: 'reply.constraints.choices_equal' },
            { nb: validationRule.number },
          ),
        };
      }
    }
  });

  const responsesErrorAvailableQuestions: ResponsesError = responsesError.map(error =>
    error && availableQuestionIds.includes(error.idQuestion) ? { value: error.value } : undefined,
  );

  return responsesErrorAvailableQuestions?.length > 0
    ? { responses: responsesErrorAvailableQuestions }
    : {};
};

export default validateResponses;
