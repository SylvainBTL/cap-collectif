<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Form\QuestionnaireCreateType;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Form\Type\UserFormType;
use Doctrine\DBAL\Driver\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;
use Psr\Log\LoggerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateQuestionnaireMutation implements MutationInterface
{
    private $em;
    private $formFactory;
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        FormFactory $formFactory,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
    }

    public function __invoke(Argument $input): array
    {
        $questionnaire = new Questionnaire();

        $form = $this->formFactory->create(QuestionnaireCreateType::class, $questionnaire);

        $form->submit($input->getRawArguments(), false);
        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . ' : ' . (string) $form->getErrors(true, false));
            throw GraphQLException::fromFormErrors($form);
        }

        $this->em->persist($questionnaire);
        $this->em->flush();

        return ['questionnaire' => $questionnaire];
    }
}
