<?php

namespace Capco\AppBundle\GraphQL\Mutation\ProposalForm;

use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Form\ProposalFormCreateType;
use Capco\AppBundle\Resolver\SettableOwnerResolver;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;

class CreateProposalFormMutation implements MutationInterface
{
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $em;
    private SettableOwnerResolver $settableOwnerResolver;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        SettableOwnerResolver $settableOwnerResolver
    ) {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->settableOwnerResolver = $settableOwnerResolver;
    }

    public function __invoke(Argument $input, User $viewer): array
    {
        $proposalForm = new ProposalForm();
        $proposalForm->setDescriptionUsingJoditWysiwyg(true);

        $form = $this->formFactory->create(ProposalFormCreateType::class, $proposalForm);

        $data = $input->getArrayCopy();
        unset($data['owner']);
        $form->submit($input->getArrayCopy(), false);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . $form->getErrors(true, false));
        }

        $proposalForm->setOwner(
            $this->settableOwnerResolver->__invoke($input->offsetGet('owner'), $viewer)
        );
        $proposalForm->setCreator($viewer);

        $this->em->persist($proposalForm);
        $this->em->flush();

        return ['proposalForm' => $proposalForm];
    }
}
