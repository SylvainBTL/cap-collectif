// @flow
import * as React from 'react';
import Modal, { type ModalProps } from '~ds/Modal/Modal';
import Heading from '~ui/Primitives/Heading';
import Button from '~ds/Button/Button';
import type { ButtonProps } from '~ds/Button/Button';
import useLoadingMachine from '~/utils/hooks/useLoadingMachine';
import { isAsync } from '~/utils/isAsync';

type Props = {|
  ...ModalProps,
  +options: {|
    +confirmButton: {|
      +content: string,
      +props?: ButtonProps,
    |},
    +cancelButton: {|
      +content: string,
      +props?: ButtonProps,
    |},
  |},
  +title: React$Node,
  +body?: React$Node,
  +onConfirm?: () => void | Promise<void>,
  +onCancel?: () => void | Promise<void>,
  +children?: React$Node,
|};

export const ConfirmModal = ({
  children,
  title,
  body,
  onConfirm,
  onCancel,
  options,
  ...props
}: Props) => {
  const { isLoading, startLoading, stopLoading } = useLoadingMachine();
  return (
    <Modal hideOnClickOutside={false} hideOnEsc={false} hideCloseButton {...props}>
      {({ hide }) => (
        <>
          <Modal.Header p={4} className="confirm-modal-header">
            <Heading>{title}</Heading>
          </Modal.Header>
          {body && <Modal.Body className="confirm-modal-body" px={4}>{body}</Modal.Body>}
          <Modal.Footer
            className="confirm-modal-footer"
            spacing={4}
            p={4}
            pt={4}
            align={['stretch', 'center']}
            direction={['column', 'row']}>
            <Button
              disabled={isLoading}
              variant="tertiary"
              justifyContent={['center', 'flex-start']}
              {...options.cancelButton.props}
              onClick={async () => {
                try {
                  if (onCancel) {
                    if (isAsync(onCancel)) {
                      startLoading();
                    }
                    await onCancel();
                    stopLoading();
                  }
                  hide();
                } catch (e) {
                  stopLoading();
                  console.error(e);
                }
              }}>
              {options.cancelButton.content}
            </Button>
            <Button
              disabled={isLoading}
              isLoading={isLoading}
              justifyContent={['center', 'flex-start']}
              variant="primary"
              variantColor="danger"
              variantSize="medium"
              {...options.confirmButton.props}
              onClick={async () => {
                try {
                  if (onConfirm) {
                    if (isAsync(onConfirm)) {
                      startLoading();
                    }
                    await onConfirm();
                    stopLoading();
                  }
                  hide();
                } catch (e) {
                  stopLoading();
                  console.error(e);
                }
              }}>
              {options.confirmButton.content}
            </Button>
          </Modal.Footer>
        </>
      )}
    </Modal>
  );
};

export default ConfirmModal;
