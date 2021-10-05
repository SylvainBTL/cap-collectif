<?php

namespace Capco\AppBundle\GraphQL\Mutation\Mailing;

use Capco\AppBundle\Entity\EmailingCampaign;
use Capco\AppBundle\Entity\MailingList;
use Capco\AppBundle\Entity\SenderEmail;
use Capco\AppBundle\Enum\CreateEmailingCampaignErrorCode;
use Capco\AppBundle\Enum\EmailingCampaignInternalList;
use Capco\AppBundle\Repository\MailingListRepository;
use Capco\AppBundle\Repository\SenderEmailRepository;
use Capco\AppBundle\SiteParameter\SiteParameterResolver;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateEmailingCampaignMutation implements MutationInterface
{
    private MailingListRepository $mailingListRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private SiteParameterResolver $siteParams;
    private SenderEmailRepository $senderEmailRepository;

    public function __construct(
        MailingListRepository $mailingListRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        SiteParameterResolver $siteParams,
        SenderEmailRepository $senderEmailRepository
    ) {
        $this->mailingListRepository = $mailingListRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->siteParams = $siteParams;
        $this->senderEmailRepository = $senderEmailRepository;
    }

    public function __invoke(Argument $input)
    {
        $emailingCampaign = $this->createDefaultCampaign();
        if ($mailingListId = $input->offsetGet('mailingList')) {
            if ($error = $this->setMailingListOrError($emailingCampaign, $mailingListId)) {
                return compact('error');
            }
        }

        $this->entityManager->persist($emailingCampaign);
        $this->entityManager->flush();

        return compact('emailingCampaign');
    }

    private function createDefaultCampaign(): EmailingCampaign
    {
        $emailingCampaign = new EmailingCampaign();
        $emailingCampaign->setName(
            $this->translator->trans('global.campaign.new', [], 'CapcoAppBundle')
        );
        $emailingCampaign->setSenderEmail($this->getDefaultEmail());
        $emailingCampaign->setSenderName(
            $this->siteParams->getValue('admin.mail.notifications.send_name') ??
                $emailingCampaign->getSenderEmail()
        );

        return $emailingCampaign;
    }

    private function setMailingListOrError(
        EmailingCampaign $emailingCampaign,
        string $mailingListId
    ): ?string {
        if (\in_array($mailingListId, EmailingCampaignInternalList::ALL)) {
            $emailingCampaign->setMailingInternal($mailingListId);
        } elseif ($mailingList = $this->findMailingList($mailingListId)) {
            $emailingCampaign->setMailingList($mailingList);
            $mailingList->setIsDeletable(false);
        } else {
            return CreateEmailingCampaignErrorCode::ID_NOT_FOUND_MAILING_LIST;
        }

        return null;
    }

    private function findMailingList(string $globalId): ?MailingList
    {
        $mailingList = null;

        $id = GlobalId::fromGlobalId($globalId)['id'];
        if ($id) {
            $mailingList = $this->mailingListRepository->find($id);
        }

        return $mailingList;
    }

    private function getDefaultEmail(): string 
    {
        $senderEmail = $this->senderEmailRepository->getDefault();

        if($senderEmail instanceOf SenderEmail) {
            $senderEmail = $senderEmail->getAddress();
        } else {
            $senderEmail = $this->siteParams->getValue('admin.mail.notifications.send_address');
        }

        return $senderEmail;
    }
}
