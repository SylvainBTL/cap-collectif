<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Elasticsearch\Indexer;
use Doctrine\DBAL\Exception\DriverException;
use Overblog\GraphQLBundle\Error\UserError;
use Psr\Log\LoggerInterface;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Capco\AppBundle\GraphQL\ConnectionBuilder;
use Capco\AppBundle\Form\EventType;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Symfony\Component\Form\FormFactoryInterface;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;

class AddEventMutation implements MutationInterface
{
    private $em;
    private $formFactory;
    private $logger;
    private $indexer;
    private $globalIdResolver;
    private $publisher;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        GlobalIdResolver $globalIdResolver,
        Indexer $indexer,
        Publisher $publisher
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->globalIdResolver = $globalIdResolver;
        $this->indexer = $indexer;
        $this->publisher = $publisher;
    }

    public function __invoke(Arg $input, User $viewer): array
    {
        $values = $input->getArrayCopy();

        if (isset($values['customCode']) && !empty($values['customCode']) && !$viewer->isAdmin()) {
            return [
                'eventEdge' => null,
                'userErrors' => [['message' => 'You are not authorized to add customCode field.']]
            ];
        }
        /** @var User $author */
        $author = isset($values['author'])
            ? $this->globalIdResolver->resolve($values['author'], $viewer)
            : null;

        // admin or superAdmin can set other user as author
        if ($author && $viewer->isAdmin()) {
            $event = (new Event())->setAuthor($author);
        } else {
            $event = (new Event())->setAuthor($viewer);
        }

        static::initEvent($event, $values, $this->formFactory);

        try {
            $this->em->persist($event);
            $this->em->flush();
        } catch (DriverException $e) {
            throw new UserError($e->getMessage());
        }

        $this->indexer->index(\get_class($event), $event->getId());
        $this->indexer->finishBulk();

        $this->publisher->publish(
            'event.create',
            new Message(
                json_encode([
                    'eventId' => $event->getId()
                ])
            )
        );

        $edge = new Edge(ConnectionBuilder::offsetToCursor(0), $event);

        return ['eventEdge' => $edge, 'userErrors' => []];
    }

    public static function initEvent(
        Event $event,
        array &$values,
        FormFactoryInterface $formFactory
    ): void {
        $event->setStartAt(new \DateTime($values['startAt']));
        unset($values['startAt']);
        if (isset($values['endAt'])) {
            $event->setEndAt(new \DateTime($values['endAt']));
            unset($values['endAt']);
        }

        unset($values['author']);

        $form = $formFactory->create(EventType::class, $event);
        $form->submit($values, false);

        if (!$form->isValid()) {
            throw GraphQLException::fromFormErrors($form);
        }
    }
}
