<?php

namespace Capco\AppBundle\EventListener;

use Capco\AppBundle\Entity\Questionnaire;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class QuestionnaireSubscriber implements EventSubscriber
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getSubscribedEvents(): array
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $questionnaire = $args->getEntity();

        if (!$questionnaire instanceof Questionnaire) {
            return;
        }

        $viewer = null;
        if ($this->tokenStorage->getToken()) {
            $viewer = $this->tokenStorage->getToken()->getUser();
        }

        $email = $viewer && $viewer->isOnlyProjectAdmin() ? $viewer->getEmail() : null;

        $questionnaire->setOwner($viewer);
        $notificationConfiguration = $questionnaire
            ->getNotificationsConfiguration()
            ->setEmail($email);

        $questionnaire->setNotificationsConfiguration($notificationConfiguration);
    }
}
