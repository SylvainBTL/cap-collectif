<?php

namespace Capco\AppBundle\GraphQL\Mutation\Sms;

use Capco\AppBundle\Entity\UserPhoneVerificationSms;
use Capco\AppBundle\Helper\TwilioHelper;
use Capco\AppBundle\Repository\UserPhoneVerificationSmsRepository;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class SendSmsPhoneValidationCodeMutation implements MutationInterface
{
    public const RETRY_LIMIT_REACHED = 'RETRY_LIMIT_REACHED';
    public const PHONE_ALREADY_CONFIRMED = 'PHONE_ALREADY_CONFIRMED';
    private const RETRY_PER_MINUTE = 2;

    private EntityManagerInterface $em;
    private TwilioHelper $twilioHelper;
    private UserPhoneVerificationSmsRepository $userPhoneVerificationSmsRepository;

    public function __construct(
        EntityManagerInterface $em,
        TwilioHelper $twilioHelper,
        UserPhoneVerificationSmsRepository $userPhoneVerificationSmsRepository
    ) {
        $this->em = $em;
        $this->twilioHelper = $twilioHelper;
        $this->userPhoneVerificationSmsRepository = $userPhoneVerificationSmsRepository;
    }

    public function __invoke(Argument $input, User $viewer): array
    {
        if ($viewer->isPhoneConfirmed()) {
            return ['errorCode' => self::PHONE_ALREADY_CONFIRMED];
        }

        if (!$this->canRetry($viewer)) {
            return ['errorCode' => self::RETRY_LIMIT_REACHED];
        }

        $to = $viewer->getPhone();

        $sendVerificationSmsErrorCode = $this->twilioHelper->sendVerificationSms($to);
        if ($sendVerificationSmsErrorCode) {
            return ['errorCode' => $sendVerificationSmsErrorCode];
        }

        $userPhoneVerificationSms = (new UserPhoneVerificationSms())
            ->setUser($viewer)
            ->setPending();
        $this->em->persist($userPhoneVerificationSms);
        $this->em->flush();

        return ['errorCode' => null];
    }

    /**
     * Check whether you can re-send a sms
     * The rule is : max 2 sms sent within 1 minute.
     */
    private function canRetry(User $viewer): bool
    {
        $userPhoneVerificationSmsList = $this->userPhoneVerificationSmsRepository->findByUserWithinOneMinuteRange(
            $viewer
        );

        return \count($userPhoneVerificationSmsList) < self::RETRY_PER_MINUTE;
    }
}
