<?php

namespace Capco\AppBundle\EventListener;

use Capco\AppBundle\CapcoAppBundleEvents;
use Capco\AppBundle\Entity\ProposalComment;
use Capco\AppBundle\Event\CommentChangedEvent;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentSubscriber implements EventSubscriberInterface
{
    /**
     * @var Publisher
     */
    private $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public static function getSubscribedEvents()
    {
        return [
            CapcoAppBundleEvents::COMMENT_CHANGED => 'onCommentChanged',
        ];
    }

    public function onCommentChanged(CommentChangedEvent $event)
    {
        $comment = $event->getComment();
        $action = $event->getAction();
        $entity = $comment->getRelatedObject();
        if ('remove' === $action) {
            $entity->decreaseCommentsCount(1);
            if ($comment instanceof ProposalComment && $comment->getProposal()->getProposalForm()->isNotifyingCommentOnDelete()) {
                $this->publisher->publish('comment.delete', new Message(
                    json_encode([
                        'username' => $comment->getAuthor()->getDisplayName(),
                        'userSlug' => $comment->getAuthor()->getSlug(),
                        'body' => $comment->getBody(),
                        'proposal' => $comment->getProposal()->getTitle(),
                        'projectSlug' => $comment->getProposal()->getProject()->getSlug(),
                        'stepSlug' => $comment->getProposal()->getProposalForm()->getStep()->getSlug(),
                        'proposalSlug' => $comment->getProposal()->getSlug(),
                    ])
                ));
            }
        } elseif ('add' === $action) {
            $entity->increaseCommentsCount(1);
            if ($comment instanceof ProposalComment && $comment->getProposal()->getProposalForm()->isNotifyingCommentOnCreate()) {
                $this->publisher->publish('comment.create', new Message(
                    json_encode([
                        'commentId' => $comment->getId(),
                    ])
                ));
            }
        } elseif ('update' === $action) {
            if ($comment instanceof ProposalComment && $comment->getProposal()->getProposalForm()->isNotifyingCommentOnUpdate()) {
                $this->publisher->publish('comment.update', new Message(
                    json_encode([
                        'commentId' => $comment->getId(),
                    ])
                ));
            }
        }
    }
}
