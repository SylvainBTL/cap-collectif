<?php

namespace Capco\AppBundle\Mailer\Message\Proposal\Analysis;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Mailer\Message\AbstractExternalMessage;

class AnalysisPublicationMessage extends AbstractExternalMessage
{
    public const SUBJECT = 'notification.proposal.analysis.title';
    public const TEMPLATE = '@CapcoMail/Proposal/analysisPublication.html.twig';
    public const FOOTER = '';

    public static function getMySubjectVars(Proposal $proposal, array $params): array
    {
        return [
            'analystName' => $params['analyst']->getUsername(),
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
            'analyst' => $params['analyst'],
            'analystUrl' => $params['analystUrl'],
            'publicationDate' => $params['publicationDate'],
        ];
    }
}
