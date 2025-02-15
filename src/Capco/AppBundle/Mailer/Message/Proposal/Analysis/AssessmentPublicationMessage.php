<?php

namespace Capco\AppBundle\Mailer\Message\Proposal\Analysis;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Mailer\Message\AbstractExternalMessage;

class AssessmentPublicationMessage extends AbstractExternalMessage
{
    public const SUBJECT = 'notification.proposal.assessment.title';
    public const TEMPLATE = '@CapcoMail/Proposal/assessmentPublication.html.twig';
    public const FOOTER = '';

    public static function getMySubjectVars(Proposal $proposal, array $params): array
    {
        return [
            'supervisorName' => $proposal->getSupervisor()->getUsername(),
            'proposalName' => $proposal->getTitle(),
        ];
    }

    public static function getMyTemplateVars(Proposal $proposal, array $params): array
    {
        return [
            'siteName' => $params['siteName'],
            'baseUrl' => $params['baseURL'],
            'siteUrl' => $params['baseURL'],
            'projectName' => $proposal->getProject()->getTitle(),
            'proposal' => $proposal,
            'proposalUrl' => $params['proposalUrl'],
            'supervisorUrl' => $params['supervisorUrl'],
            'publicationDate' => $params['publicationDate'],
        ];
    }
}
