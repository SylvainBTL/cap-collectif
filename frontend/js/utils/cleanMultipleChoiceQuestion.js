// @flow
import { type IntlShape } from 'react-intl';

type MultipleChoiceQuestion = {
  +choices: {|
    +edges: ?$ReadOnlyArray<?{|
      +node: ?{|
        +title: string,
        +responses: {|
          +totalCount: number,
        |},
      |},
    |}>,
  |},
  +isOtherAllowed: boolean,
  +otherResponses: {|
    +totalCount: number,
  |},
  +$refType: any,
};

export const cleanMultipleChoiceQuestion = (
  multipleChoiceQuestion: MultipleChoiceQuestion,
  intl: IntlShape,
) => {
  let data = multipleChoiceQuestion?.choices?.edges
    ?.filter(Boolean)
    .map(edge => edge.node)
    .filter(Boolean)
    .filter(choice => choice.responses.totalCount > 0)
    .reduce((acc, curr) => {
      acc.push({
        name: curr.title,
        value: curr.responses.totalCount,
      });
      return acc;
    }, []);

  if (!data) {
    data = [];
  }

  if (
    multipleChoiceQuestion &&
    multipleChoiceQuestion.isOtherAllowed &&
    multipleChoiceQuestion.otherResponses &&
    multipleChoiceQuestion.otherResponses.totalCount !== 0
  ) {
    data.push({
      name: intl.formatMessage({ id: 'gender.other' }),
      value:
        multipleChoiceQuestion.otherResponses && multipleChoiceQuestion.otherResponses.totalCount,
    });
  }

  return data;
};
