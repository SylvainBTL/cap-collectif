<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\AbstractReply;
use Swarrot\Broker\Message;
use Capco\AppBundle\Entity\Reply;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Capco\AppBundle\Repository\ReplyRepository;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Capco\AppBundle\Notifier\QuestionnaireReplyNotifier;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class DeleteUserReplyMutation implements MutationInterface
{
    private EntityManagerInterface $em;
    private ReplyRepository $replyRepo;
    private Publisher $publisher;
    private Indexer $indexer;

    public function __construct(
        EntityManagerInterface $em,
        ReplyRepository $replyRepo,
        Publisher $publisher,
        Indexer $indexer
    ) {
        $this->em = $em;
        $this->replyRepo = $replyRepo;
        $this->publisher = $publisher;
        $this->indexer = $indexer;
    }

    public function __invoke(string $id, User $viewer): array
    {
        /** @var Reply $reply */
        $reply = $this->replyRepo->find(GlobalId::fromGlobalId($id)['id']);

        if (!$reply) {
            throw new UserError('Reply not found');
        }

        if ($viewer->getId() !== $reply->getAuthor()->getId()) {
            throw new UserError('You are not the author of this reply');
        }

        $questionnaire = $reply->getQuestionnaire();

        $this->indexer->remove(AbstractReply::class, $reply->getId());
        $this->indexer->finishBulk();

        $this->em->remove($reply);
        $this->em->flush();

        if ($questionnaire && $questionnaire->isNotifyResponseDelete()) {
            $this->publisher->publish(
                'questionnaire.reply',
                new Message(
                    json_encode([
                        'reply' => [
                            'author_slug' => $reply->getAuthor()->getSlug(),
                            'deleted_at' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
                            'project_title' => $reply
                                ->getStep()
                                ->getProject()
                                ->getTitle(),
                            'questionnaire_step_title' => $reply->getStep()->getTitle(),
                            'questionnaire_id' => $reply->getQuestionnaire()->getId(),
                            'author_name' => $reply->getAuthor()->getUsername(),
                            'is_anon_reply' => false,
                        ],
                        'state' => QuestionnaireReplyNotifier::QUESTIONNAIRE_REPLY_DELETE_STATE,
                    ])
                )
            );
        }

        return ['questionnaire' => $questionnaire, 'replyId' => $id];
    }
}
