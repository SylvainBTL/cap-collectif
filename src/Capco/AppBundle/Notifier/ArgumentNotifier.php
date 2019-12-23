<?php

namespace Capco\AppBundle\Notifier;

use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\GraphQL\Resolver\Argument\ArgumentUrlResolver;
use Capco\AppBundle\GraphQL\Resolver\User\UserUrlResolver;
use Capco\AppBundle\Mailer\MailerService;
use Capco\AppBundle\Mailer\Message\Argument\NewArgumentModeratorMessage;
use Capco\AppBundle\Mailer\Message\Argument\TrashedArgumentAuthorMessage;
use Capco\AppBundle\Mailer\Message\Argument\UpdateArgumentModeratorMessage;
use Capco\AppBundle\SiteParameter\Resolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ArgumentNotifier extends BaseNotifier
{
    protected $argumentUrlResolver;
    protected $translator;
    protected $userUrlResolver;

    public function __construct(
        MailerService $mailer,
        Resolver $siteParams,
        ArgumentUrlResolver $argumentUrlResolver,
        RouterInterface $router,
        TranslatorInterface $translator,
        UserUrlResolver $userUrlResolver
    ) {
        parent::__construct($mailer, $siteParams, $router);
        $this->argumentUrlResolver = $argumentUrlResolver;
        $this->translator = $translator;
        $this->userUrlResolver = $userUrlResolver;
    }

    public function onCreation(Argument $argument)
    {
        $consultation = $argument->getStep()->getFirstConsultation();

        if ($consultation && $consultation->isModeratingOnCreate()) {
            $this->mailer->sendMessage(
                NewArgumentModeratorMessage::create(
                    $argument,
                    $this->siteParams->getValue('admin.mail.notifications.receive_address'),
                    null,
                    $this->argumentUrlResolver->__invoke($argument),
                    $this->userUrlResolver->__invoke($argument->getAuthor()),
                    $this->router,
                    $this->translator
                )
            );
        }
    }

    public function onUpdate(Argument $argument)
    {
        $consultation = $argument->getStep()->getFirstConsultation();

        if ($consultation && $consultation->isModeratingOnUpdate()) {
            $this->mailer->sendMessage(
                UpdateArgumentModeratorMessage::create(
                    $argument,
                    $this->siteParams->getValue('admin.mail.notifications.receive_address'),
                    null,
                    $this->argumentUrlResolver->__invoke($argument),
                    $this->userUrlResolver->__invoke($argument->getAuthor()),
                    $this->router,
                    $this->translator
                )
            );
        }
    }

    public function onTrash(Argument $argument)
    {
        $this->mailer->sendMessage(
            TrashedArgumentAuthorMessage::create(
                $argument,
                $this->argumentUrlResolver->__invoke($argument)
            )
        );
    }
}
