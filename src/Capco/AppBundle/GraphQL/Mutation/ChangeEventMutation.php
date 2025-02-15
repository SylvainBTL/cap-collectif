<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\DBAL\Enum\EventReviewStatusType;
use Capco\AppBundle\Entity\Event;
use Capco\AppBundle\GraphQL\Mutation\Event\AbstractEventMutation;
use Capco\AppBundle\GraphQL\Mutation\Locale\LocaleUtils;
use Capco\AppBundle\Security\EventVoter;
use Capco\UserBundle\Entity\User;
use Doctrine\Common\Util\ClassUtils;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Error\UserError;
use Swarrot\Broker\Message;

class ChangeEventMutation extends AbstractEventMutation
{
    public function __invoke(Arg $input, User $viewer): array
    {
        $values = $input->getArrayCopy();

        try {
            self::checkCustomCode($values, $viewer);
            $event = $this->getEvent($values['id'], $viewer);
            unset($values['id']);
            $this->setAuthor($event, $values, $viewer);
            LocaleUtils::indexTranslations($values);
            $this->setProjects($event, $viewer, $values);
            $this->setSteps($event, $viewer, $values);
            unset($values['projects']);
        } catch (UserError $error) {
            return [
                'eventEdge' => null,
                'userErrors' => [['message' => $error->getMessage()]],
            ];
        }

        // a user want to edit his refused event
        if (
            !$viewer->isAdmin() &&
            EventReviewStatusType::REFUSED === $event->getStatus() &&
            $event->getReview()
        ) {
            $event->getReview()->setStatus(EventReviewStatusType::AWAITING);
        }

        $this->submitEventFormData($event, $values, $this->formFactory);

        $this->em->flush();

        $this->indexer->index(ClassUtils::getClass($event), $event->getId());
        $this->indexer->finishBulk();

        if (!$viewer->isProjectAdmin()) {
            $this->publisher->publish(
                'event.update',
                new Message(
                    json_encode([
                        'eventId' => $event->getId(),
                    ])
                )
            );
        }

        return ['event' => $event, 'userErrors' => []];
    }

    public function isGranted(string $eventId, User $viewer): bool
    {
        $event = $this->globalIdResolver->resolve($eventId, $viewer);

        if (!$event) {
            return false;
        }

        return $this->authorizationChecker->isGranted(EventVoter::EDIT, $event);
    }

    private function setAuthor(Event $event, array $values, User $viewer): void
    {
        $author = $event->getAuthor();

        if ($viewer->isAdmin() && isset($values['author']) && !empty($values['author'])) {
            $author = $this->getUser($values['author'], $viewer);
        }

        $event->setAuthor($author);
    }
}
