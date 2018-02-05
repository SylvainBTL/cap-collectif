<?php

namespace Capco\AppBundle\Mailer\Message\Argument;

use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Mailer\Message\ModeratorMessage;
use Symfony\Component\Routing\RouterInterface;

final class UpdateArgumentModeratorMessage extends ModeratorMessage
{
    public static function create(Argument $argument, string $moderatorEmail, string $moderatorName = null, string $argumentLink, string $authorLink, RouterInterface $router): self
    {
        $message = new self(
            $moderatorEmail,
            $moderatorName,
            'notification-subject-modified-argument',
            static::getMySubjectVars(
                $argument->getAuthor()->getUsername(),
                $argument->getRelated()->getTitle()
            ),
            'notification-content-modified-argument',
            static::getMyTemplateVars(
                $argument->getTypeAsString(),
                $argument->getBody(),
                $argument->getUpdatedAt()->format('d/m/Y'),
                $argument->getUpdatedAt()->format('H:i:s'),
                $argument->getAuthor()->getUsername(),
                $authorLink,
                $argumentLink
            )
        );
        $message->generateModerationLinks($argument, $router);

        return $message;
    }

    private static function getMyTemplateVars(
        string $type,
        string $body,
        string $updatedDate,
        string $updatedTime,
        string $authorName,
        string $authorLink,
        string $argumentLink
    ): array {
        return [
            '{type}' => $type,
            '{body}' => self::escape($body),
            '{updatedDate}' => $updatedDate,
            '{updatedTime}' => $updatedTime,
            '{authorName}' => self::escape($authorName),
            '{authorLink}' => $authorLink,
            '{argumentLink}' => $argumentLink,
        ];
    }

    private static function getMySubjectVars(
        string $authorName,
        string $projectName
    ): array {
        return [
            '{projectName}' => self::escape($projectName),
            '{authorName}' => self::escape($authorName),
        ];
    }
}
