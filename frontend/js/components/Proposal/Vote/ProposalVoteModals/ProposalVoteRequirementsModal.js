// @flow
import React from 'react';
import {Flex, MultiStepModal, Heading, Button, useMultiStepModal, CapUIIcon, Text} from '@cap-collectif/ui';
import type { IntlShape } from 'react-intl';
import moment from 'moment';
import {useIntl} from "react-intl";
import RequirementsForm from '~/components/Requirements/RequirementsForm';
import UpdateProfilePersonalDataMutation from '~/mutations/UpdateProfilePersonalDataMutation';
import { mutationErrorToast } from '~/components/Utils/MutationErrorToast';
import SendSmsPhoneValidationCodeMutation from '~/mutations/SendSmsPhoneValidationCodeMutation';
import type { SendSmsPhoneValidationCodeMutationResponse } from '~relay/SendSmsPhoneValidationCodeMutation.graphql';
import type { UpdateProfilePersonalDataMutationResponse } from '~/mutations/UpdateProfilePersonalDataMutation';
import type { Uuid } from '~/types';
import CheckIdentificationCodeMutation from '~/mutations/CheckIdentificationCodeMutation';
import UpdateRequirementMutation from '~/mutations/UpdateRequirementMutation';
import ResetCss from '~/utils/ResetCss';

export const formName = 'vote-requirements-form';

export type onRequirementsSubmitDataProps = {|
  +PhoneVerifiedRequirement?: {
    CountryCode: string,
    phoneNumber: string,
  },
  +PhoneRequirement?: {
    CountryCode: string,
    phoneNumber: string,
  },
  +FranceConnectRequirement?: boolean,
  +franceConnect_: boolean,
  +FirstnameRequirement?: string,
  +LastnameRequirement?: string,
  +DateOfBirthRequirement?: moment,
  +IdentificationCodeRequirement?: string,
  +PostalAddressRequirement?: string,
  +realAddress?: {|
    address_components: Array<{
      long_name: string,
      short_name: string,
      types: string[],
    }>,
    formatted_address: string,
    geometry: {
      location: {|
        +lat: number,
        +lng: number,
      |},
      location_type: string,
      viewport?: {
        Va: {
          i: number,
          j: number,
        },
        Za: {
          i: number,
          j: number,
        },
      },
    },
    place_id?: string,
    plus_code?: {
      compound_code: string,
      global_code: string,
    },
    types: string[],
  |},
  +CheckboxRequirement: {
    viewerMeetsTheRequirement: boolean,
    label: string,
    id: Uuid,
  }[],
|};

const getPhone = (requirement: { CountryCode: string, phoneNumber: string }) => {
  return `${requirement.CountryCode}${
    requirement?.phoneNumber.split('')[0] === '0'
      ? requirement.phoneNumber.slice(1)
      : requirement.phoneNumber
  }`;
};

export const onRequirementsSubmit = async (
  data: onRequirementsSubmitDataProps,
  goToNextStep: () => void,
  isPhoneVerificationOnly: boolean,
  intl: IntlShape,
  setIsLoading: (value: boolean) => void,
  needToVerifyPhone: boolean,
  setError: any,
) => {
  if (data.IdentificationCodeRequirement && data.IdentificationCodeRequirement !== '') {
    try {
      // Check the identification code
      const CheckIdentificationCodeResponse = await CheckIdentificationCodeMutation.commit({
        input: { identificationCode: data.IdentificationCodeRequirement },
      });
      if (CheckIdentificationCodeResponse.checkIdentificationCode?.errorCode) {
        setError('IdentificationCodeRequirement', {
          type: 'value',
          message: intl.formatMessage({
            id: CheckIdentificationCodeResponse.checkIdentificationCode.errorCode || '',
          }),
        });
        return;
      }
    } catch (e) {
      mutationErrorToast(intl);
    }
  }

  if (data.CheckboxRequirement && data.CheckboxRequirement.length > 0) {
    try {
      const allCheckboxRequirementRequest = data.CheckboxRequirement.map(checkboxRequirement => {
        return UpdateRequirementMutation.commit({
          input: {
            requirement: checkboxRequirement.id,
            value: checkboxRequirement.viewerMeetsTheRequirement,
          },
        });
      });

      Promise.all(allCheckboxRequirementRequest).catch(() => {
        mutationErrorToast(intl);
      });
    } catch (e) {
      mutationErrorToast(intl);
    }
  }

  const phone = data.PhoneVerifiedRequirement
    ? getPhone(data.PhoneVerifiedRequirement)
    : data.PhoneRequirement
    ? getPhone(data.PhoneRequirement)
    : undefined;

  let input;
  if (isPhoneVerificationOnly) {
    input = { phone };
  } else {
    input = {
      phone,
      firstname: data.FirstnameRequirement,
      lastname: data.LastnameRequirement,
      dateOfBirth: data.DateOfBirthRequirement
        ? moment(data.DateOfBirthRequirement).format()
        : undefined,
      userIdentificationCode: data.IdentificationCodeRequirement,
      postalAddress: data.realAddress ? JSON.stringify([data.realAddress]) : undefined,
    };
  }

  try {
    setIsLoading(true);
    const updateResponse: UpdateProfilePersonalDataMutationResponse =
      await UpdateProfilePersonalDataMutation.commit({ input });

    if (updateResponse) {
      if (
        updateResponse.updateProfilePersonalData?.errorCode === 'PHONE_ALREADY_USED_BY_ANOTHER_USER'
      ) {
        if (data.PhoneVerifiedRequirement) {
          setError('PhoneVerifiedRequirement.phoneNumber', {
            type: 'value',
            message: intl.formatMessage({
              id: updateResponse.updateProfilePersonalData?.errorCode,
            }),
          });
          setIsLoading(false);
          return false;
        }

        setError('PhoneRequirement.phoneNumber', {
          type: 'value',
          message: intl.formatMessage({
            id: updateResponse.updateProfilePersonalData?.errorCode,
          }),
        });
        setIsLoading(false);
        return false;
      }

      try {
        if (needToVerifyPhone) {
          const smsResponse: SendSmsPhoneValidationCodeMutationResponse =
            await SendSmsPhoneValidationCodeMutation.commit({ input: {} });
          if (smsResponse) {
            setIsLoading(false);
            goToNextStep();
          }
        } else {
          setIsLoading(false);
          goToNextStep();
        }
      } catch (e) {
        setIsLoading(false);

        mutationErrorToast(intl);
      }
    }
  } catch (e) {
    setIsLoading(false);

    mutationErrorToast(intl);
  }
};
export type ProposalVoteRequirementsModalProps = {|
  +initialValues: { [key: string]: any },
  +isPhoneVerificationOnly: boolean,
  +requirementsForm: any,
  +isLoading: boolean,
  +setIsLoading: (isLoading: boolean) => void,
  +hasPhoneRequirements: boolean,
  +needToVerifyPhone: boolean,
  +modalTitle: string,
|};

const ProposalVoteRequirementsModal = ({
  initialValues,
  isPhoneVerificationOnly,
  requirementsForm,
  isLoading,
  setIsLoading,
  hasPhoneRequirements,
  needToVerifyPhone,
  modalTitle
}: ProposalVoteRequirementsModalProps) => {
  const intl = useIntl();
  const { goToNextStep, hide } = useMultiStepModal();
  const { control, formState, trigger, setValue } = requirementsForm;

  const onClick = e => {
    requirementsForm.handleSubmit(data => {
      onRequirementsSubmit(
        data,
        goToNextStep,
        isPhoneVerificationOnly,
        intl,
        setIsLoading,
        hasPhoneRequirements,
        requirementsForm.setError,
      );
    })(e);
  }

  return (
    <>
      <ResetCss>
        <MultiStepModal.Header>
          <Text uppercase color="neutral-gray.500" fontWeight={700} fontSize={1} lineHeight="sm">{intl.formatMessage({ id: modalTitle })}</Text>
          <Heading>{intl.formatMessage({ id: 'requirements' })}</Heading>
        </MultiStepModal.Header>
      </ResetCss>
      <MultiStepModal.Body>
        <Flex align="flex-start">
          <RequirementsForm
            control={control}
            formState={formState}
            isPhoneVerificationOnly={isPhoneVerificationOnly}
            initialValues={initialValues}
            trigger={trigger}
            setValue={setValue}
          />
        </Flex>
      </MultiStepModal.Body>
      <MultiStepModal.Footer>
        <Button
          onClick={hide}
          variant="secondary"
          variantColor="hierarchy"
          variantSize="medium"
        >
          {intl.formatMessage({ id: 'global.cancel' })}
        </Button>
        <Button
          variantSize="medium"
          variant="secondary"
          isLoading={requirementsForm.formState.isSubmitting || isLoading}
          disabled={Object.keys(requirementsForm.formState.errors).length > 0}
          rightIcon={CapUIIcon.LongArrowRight}
          onClick={onClick}
        >
          {needToVerifyPhone
            ? intl.formatMessage({ id: 'verify.number' })
            : intl.formatMessage({ id: 'global.continue' })}
        </Button>
      </MultiStepModal.Footer>
    </>
  );
};

export default ProposalVoteRequirementsModal;
