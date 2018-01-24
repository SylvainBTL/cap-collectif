<?php

namespace Capco\AppBundle\Mailer\Message;

use Capco\AppBundle\Entity\Argument;

final class UpdateArgumentModeratorMessage extends Message
{
    public static function create(Argument $argument, string $moderatorEmail, string $moderatorName, string $argumentLink, string $authorLink): self
    {
        return new self(
            $moderatorEmail,
            $moderatorName,
            'notification-subject-modified-argument',
            static::getMySubjectVars(
                $argument->getAuthor()->getUsername(),
                $argument->getRelated()->getTitle(),
            ),
            'notification-content-modified-argument',
            static::getMyTemplateVars(
                $argument->getType(),
                $argument->getBody(),
                $argument->getUpdatedAt()->format('d/m/Y'),
                $argument->getUpdatedAt()->format('H:i:s'),
                $argument->getAuthor()->getUsername(),
                $authorLink,
                $argumentLink
            )
        );
    }

    private static function getMyTemplateVars(
        int $type,
        string $body,
        string $updatedDate,
        string $updatedTime,
        string $authorName,
        string $authorLink,
        string $argumentLink
    ): array {
        return [
            '%type%' => $type,
            '%body%' => self::escape($body),
            '%updatedDate%' => $updatedDate,
            '%updatedTime%' => $updatedTime,
            '%authorName%' => self::escape($authorName),
            '%authorLink%' => $authorLink,
            '%argumentLink%' => $argumentLink,
        ];
    }

    private static function getMySubjectVars(
        string $authorName,
        string $projectName,
    ): array {
        return [
            'projectName' => self::escape($projectName),
            'authorName' => self::escape($authorName),
        ];
    }
}
