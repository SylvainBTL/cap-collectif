<?php

namespace Capco\AppBundle\Notifier;

use Capco\AppBundle\Entity\Event;
use Capco\AppBundle\GraphQL\Resolver\Event\EventUrlResolver;
use Capco\AppBundle\Mailer\MailerService;
use Capco\AppBundle\Mailer\Message\Event\EventCreateAdminMessage;
use Capco\AppBundle\Mailer\Message\Event\EventDeleteAdminMessage;
use Capco\AppBundle\Mailer\Message\Event\EventDeleteMessage;
use Capco\AppBundle\Mailer\Message\Event\EventEditAdminMessage;
use Capco\AppBundle\Mailer\Message\Event\EventReviewMessage;
use Capco\AppBundle\Repository\EventRepository;
use Capco\AppBundle\Resolver\LocaleResolver;
use Capco\AppBundle\SiteParameter\SiteParameterResolver;
use Capco\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class EventNotifier extends BaseNotifier
{
    private $eventUrlResolver;
    private $eventRepository;

    public function __construct(
        MailerService $mailer,
        SiteParameterResolver $siteParams,
        EventUrlResolver $eventUrlResolver,
        RouterInterface $router,
        EventRepository $eventRepository,
        LocaleResolver $localeResolver
    ) {
        parent::__construct($mailer, $siteParams, $router, $localeResolver);
        $this->eventUrlResolver = $eventUrlResolver;
        $this->siteParams = $siteParams;
        $this->eventRepository = $eventRepository;
    }

    public function onCreate(Event $event): bool
    {
        return $this->mailer->createAndSendMessage(EventCreateAdminMessage::class, $event, [
            'eventURL' => $this->eventUrlResolver->__invoke($event, true),
            'username' => 'admin'
        ]);
    }

    public function onUpdate(Event $event): bool
    {
        return $this->mailer->createAndSendMessage(EventEditAdminMessage::class, $event, [
            'eventURL' => $this->eventUrlResolver->__invoke($event, true),
            'username' => 'admin'
        ]);
    }

    public function onDelete(array $event): array
    {
        $eventParticipants = $event['eventParticipants'] ?? null;
        /** @var Event $event */
        $event = $this->eventRepository->find($event['eventId']);

        if (!$event) {
            throw new NotFoundHttpException('event not found');
        }

        $this->mailer->createAndSendMessage(EventDeleteAdminMessage::class, $event, ['username' => 'admin']);
        $messages = [];

        if (!empty($eventParticipants)) {
            foreach ($eventParticipants as $participant) {
                $recipient = null;
                if (isset($participant['username']) && !empty($participant['username'])) {
                    $recipient = new User();
                    $recipient->setEmail($participant['email']);
                    $recipient->setUsername($participant['username']);
                } elseif (isset($participant['u_username']) && !empty($participant['u_username'])) {
                    $recipient = new User();
                    $recipient->setEmail($participant['u_email']);
                    $recipient->setUsername($participant['u_username']);
                }
                if ($recipient) {
                    $messages[$recipient->getUsername()] = $this->mailer->createAndSendMessage(
                        EventDeleteMessage::class,
                        $event,
                        ['eventURL' => null, 'username' => $recipient->getUsername()],
                        $recipient
                    );
                }
            }
        }

        return $messages;
    }

    public function onReview(Event $event): bool
    {
        if (!$event->getAuthor()) {
            throw new \RuntimeException('Event author cant be empty');
        }
        if (!$event->getReview()) {
            throw new \RuntimeException('Event review cant be empty');
        }

        return $this->mailer->createAndSendMessage(
            EventReviewMessage::class,
            $event,
            [],
            $event->getAuthor()
        );
    }
}
