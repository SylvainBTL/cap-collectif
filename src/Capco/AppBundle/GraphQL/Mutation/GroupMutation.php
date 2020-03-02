<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Group;
use Capco\AppBundle\Entity\UserGroup;
use Capco\AppBundle\Form\GroupCreateType;
use Capco\AppBundle\Repository\GroupRepository;
use Capco\AppBundle\Repository\UserGroupRepository;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class GroupMutation implements MutationInterface
{
    private $logger;
    private $entityManager;
    private $formFactory;
    private $userRepository;
    private $userGroupRepository;
    private $groupRepository;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        UserGroupRepository $userGroupRepository,
        GroupRepository $groupRepository
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->formFactory = $formFactory;
        $this->groupRepository = $groupRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->userRepository = $userRepository;
    }

    public function create(Argument $input): array
    {
        $group = new Group();

        $form = $this->formFactory->create(GroupCreateType::class, $group);

        $form->submit($input->getArrayCopy(), false);

        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . ' create: ' . (string) $form->getErrors(true, false));

            throw new UserError('Can\'t create this group.');
        }

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return ['group' => $group];
    }

    public function update(Argument $input): array
    {
        $arguments = $input->getArrayCopy();
        $group = $this->groupRepository->find($arguments['groupId']);

        if (!$group) {
            throw new UserError(sprintf('Unknown group with id "%d"', $arguments['groupId']));
        }

        unset($arguments['groupId']);

        $form = $this->formFactory->create(GroupCreateType::class, $group);
        $form->submit($arguments, false);

        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . ' update: ' . (string) $form->getErrors(true, false));

            throw new UserError('Can\'t update this group.');
        }

        $this->entityManager->flush();

        return ['group' => $group];
    }

    public function delete(string $groupId): array
    {
        $group = $this->groupRepository->find($groupId);

        if (!$group) {
            throw new UserError(sprintf('Unknown group with id "%s"', $groupId));
        }

        try {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error(__METHOD__ . ' delete: ' . $group->getId());

            throw new UserError('Can\'t delete this group.');
        }

        return ['deletedGroupTitle' => $group->getTitle()];
    }

    public function deleteUserInGroup(string $userId, string $groupId): array
    {
        $userId = GlobalId::fromGlobalId($userId)['id'];
        $userGroup = $this->userGroupRepository->findOneBy([
            'user' => $userId,
            'group' => $groupId
        ]);

        if (!$userGroup) {
            $error = sprintf('Cannot find the user "%u" in group "%g"', $userId, $groupId);

            $this->logger->error(__METHOD__ . ' deleteUserInGroup: ' . $error);

            throw new UserError('Can\'t delete this user in group.');
        }

        $group = $userGroup->getGroup();

        $this->entityManager->remove($userGroup);
        $this->entityManager->flush();

        return ['group' => $group];
    }

    public function addUsersInGroup(array $users, string $groupId): array
    {
        /** @var Group $group */
        $group = $this->groupRepository->find($groupId);

        if (!$group) {
            $error = sprintf('Cannot find the group "%g"', $groupId);
            $this->logger->error(__METHOD__ . ' addUsersInGroup: ' . $error);

            throw new UserError('Can\'t add users in group.');
        }

        try {
            foreach ($users as $userId) {
                $userId = GlobalId::fromGlobalId($userId)['id'];
                /** @var User $user */
                $user = $this->userRepository->find($userId);

                if ($user) {
                    $userGroup = $this->userGroupRepository->findOneBy([
                        'user' => $user,
                        'group' => $group
                    ]);

                    if (!$userGroup) {
                        $userGroup = new UserGroup();
                        $userGroup->setUser($user)->setGroup($group);

                        $this->entityManager->persist($userGroup);
                    }
                }
            }

            $this->entityManager->flush();

            return ['group' => $group];
        } catch (\Exception $e) {
            $this->logger->error(
                __METHOD__ .
                    ' addUsersInGroup: ' .
                    sprintf('Cannot add users in group with id "%g"', $groupId)
            );

            throw new UserError('Can\'t add users in group.');
        }
    }
}
