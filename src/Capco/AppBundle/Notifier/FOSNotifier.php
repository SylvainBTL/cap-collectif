<?php

namespace Capco\AppBundle\Notifier;

use Capco\AppBundle\GraphQL\Resolver\User\UserRegistrationConfirmationUrlResolver;
use Capco\AppBundle\GraphQL\Resolver\User\UserResettingPasswordUrlResolver;
use Capco\AppBundle\GraphQL\Resolver\User\UserUrlResolver;
use Capco\AppBundle\Mailer\MailerService;
use Capco\AppBundle\Mailer\Message\User\UserRegistrationConfirmationMessage;
use Capco\AppBundle\Mailer\Message\User\UserResettingPasswordMessage;
use Capco\AppBundle\Resolver\LocaleResolver;
use Capco\AppBundle\SiteParameter\SiteParameterResolver;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

class FOSNotifier extends BaseNotifier implements MailerInterface
{
    private $userUrlResolver;
    private $userResettingPasswordUrlResolver;
    private $userRegistrationConfirmationUrlResolver;
    private $logger;

    public function __construct(
        RouterInterface $router,
        MailerService $mailer,
        SiteParameterResolver $siteParams,
        UserUrlResolver $userUrlResolver,
        UserResettingPasswordUrlResolver $userResettingPasswordUrlResolver,
        UserRegistrationConfirmationUrlResolver $userRegistrationConfirmationUrlResolver,
        LoggerInterface $logger,
        LocaleResolver $localeResolver
    ) {
        parent::__construct($mailer, $siteParams, $router, $localeResolver);
        $this->userUrlResolver = $userUrlResolver;
        $this->userResettingPasswordUrlResolver = $userResettingPasswordUrlResolver;
        $this->userRegistrationConfirmationUrlResolver = $userRegistrationConfirmationUrlResolver;
        $this->logger = $logger;
    }

    /**
     * Send an email to a user to confirm the account creation.
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        if (empty($user->getEmail())) {
            $this->logger->error(__METHOD__ . ' user email can not be empty');

            return;
        }
        if (null === $user->getConfirmationToken()) {
            $this->logger->error(__METHOD__ . ' user must have confirmation token');

            return;
        }
        $this->mailer->createAndSendMessage(
            UserRegistrationConfirmationMessage::class,
            $user,
            [
                'profileURL' => $this->userUrlResolver->__invoke($user),
                'confirmationURL' => $this->userRegistrationConfirmationUrlResolver->__invoke($user)
            ],
            $user
        );
    }

    /**
     * Send an email to a user to confirm the password reset.
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $this->mailer->createAndSendMessage(
            UserResettingPasswordMessage::class,
            $user,
            ['confirmationURL' => $this->userResettingPasswordUrlResolver->__invoke($user)],
            $user
        );
    }
}
