<?php
namespace Capco\AppBundle\GraphQL\Resolver\Comment;

use Capco\AppBundle\Entity\Comment;
use Capco\AppBundle\Entity\Interfaces\Trashable;

class CommentPublicationStatusResolver
{
    public function __invoke(Comment $comment): string
    {
        if ($comment->isExpired()) {
            return 'EXPIRED';
        }

        if ($comment->isTrashed()) {
            if ($comment->getTrashedStatus() === Trashable::STATUS_VISIBLE) {
                return 'TRASHED';
            }

            return 'TRASHED_NOT_VISIBLE';
        }

        return 'PUBLISHED';
    }
}
