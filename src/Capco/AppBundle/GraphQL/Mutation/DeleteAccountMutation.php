<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Enum\DeleteAccountType;
use Capco\AppBundle\GraphQL\DataLoader\Proposal\ProposalAuthorDataLoader;
use Capco\AppBundle\Anonymizer\AnonymizeUser;
use Capco\AppBundle\Repository\AbstractResponseRepository;
use Capco\AppBundle\Repository\CommentRepository;
use Capco\AppBundle\Repository\EventRepository;
use Capco\AppBundle\Repository\HighlightedContentRepository;
use Capco\AppBundle\Repository\MailingListRepository;
use Capco\AppBundle\Repository\MediaResponseRepository;
use Capco\AppBundle\Repository\NewsletterSubscriptionRepository;
use Capco\AppBundle\Repository\ProposalEvaluationRepository;
use Capco\AppBundle\Repository\ReportingRepository;
use Capco\AppBundle\Repository\ValueResponseRepository;
use Capco\MediaBundle\Repository\MediaRepository;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Capco\UserBundle\Doctrine\UserManager;
use Overblog\GraphQLBundle\Error\UserError;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Psr\Log\LoggerInterface;
use Sonata\MediaBundle\Provider\ImageProvider;
use Capco\UserBundle\Repository\UserRepository;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Capco\AppBundle\Repository\UserGroupRepository;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteAccountMutation extends BaseDeleteUserMutation
{
    public const CANNOT_DELETE_SUPER_ADMIN = 'CANNOT_DELETE_SUPER_ADMIN';
    public const CANNOT_FIND_USER = 'Can not find this userId !';

    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        UserGroupRepository $groupRepository,
        UserManager $userManager,
        RedisStorageHelper $redisStorageHelper,
        ImageProvider $mediaProvider,
        ProposalAuthorDataLoader $proposalAuthorDataLoader,
        CommentRepository $commentRepository,
        ProposalEvaluationRepository $proposalEvaluationRepository,
        AbstractResponseRepository $abstractResponseRepository,
        NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        MediaRepository $mediaRepository,
        MediaResponseRepository $mediaResponseRepository,
        ValueResponseRepository $valueResponseRepository,
        ReportingRepository $reportingRepository,
        EventRepository $eventRepository,
        HighlightedContentRepository $highlightedContentRepository,
        MailingListRepository $mailingListRepository,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        AnonymizeUser $anonymizeUser,
        Publisher $publisher
    ) {
        parent::__construct(
            $em,
            $mediaProvider,
            $translator,
            $redisStorageHelper,
            $groupRepository,
            $userManager,
            $proposalAuthorDataLoader,
            $commentRepository,
            $proposalEvaluationRepository,
            $abstractResponseRepository,
            $newsletterSubscriptionRepository,
            $mediaRepository,
            $mediaResponseRepository,
            $valueResponseRepository,
            $reportingRepository,
            $eventRepository,
            $highlightedContentRepository,
            $mailingListRepository,
            $logger,
            $formFactory,
            $anonymizeUser,
            $publisher
        );
        $this->userRepository = $userRepository;
    }

    public function __invoke(Arg $input, User $viewer): array
    {
        $user = $this->getUser($input, $viewer);
        $userId = GlobalId::toGlobalId('User', $user->getId());
        if (!$viewer->isSuperAdmin() && $user->isSuperAdmin()) {
            return ['errorCode' => self::CANNOT_DELETE_SUPER_ADMIN, 'userId' => $userId];
        }

        $this->deleteAccount($input['type'], $user);

        return ['userId' => $userId];
    }

    public function deleteAccount(string $deleteType, User $user): void
    {
        if (DeleteAccountType::HARD === $deleteType && $user) {
            $this->hardDeleteUserContributionsInActiveSteps($user);
            // in order not to reference dead relationships between entities
            $this->em->refresh($user);
        }
        $this->anonymizeUser($user);
        $this->em->refresh($user);
        $this->softDelete($user);

        $this->em->flush();
    }

    private function getUser(Arg $input, User $viewer): User
    {
        $user = $viewer;

        if ($viewer->isAdmin() && isset($input['userId'])) {
            $userId = GlobalId::fromGlobalId($input['userId'])['id'];
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new UserError(self::CANNOT_FIND_USER);
            }
        }

        return $user;
    }
}
