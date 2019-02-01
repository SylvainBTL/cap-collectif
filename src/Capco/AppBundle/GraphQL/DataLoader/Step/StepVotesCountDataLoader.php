<?php

namespace Capco\AppBundle\GraphQL\DataLoader\Step;

use Capco\AppBundle\DataCollector\GraphQLCollector;
use Psr\Log\LoggerInterface;
use Capco\AppBundle\Cache\RedisTagCache;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Overblog\PromiseAdapter\PromiseAdapterInterface;
use Capco\AppBundle\GraphQL\DataLoader\BatchDataLoader;
use Capco\AppBundle\Repository\ProposalCollectVoteRepository;
use Capco\AppBundle\Repository\ProposalSelectionVoteRepository;

class StepVotesCountDataLoader extends BatchDataLoader
{
    private $proposalCollectVoteRepository;
    private $proposalSelectionVoteRepository;

    public function __construct(
        PromiseAdapterInterface $promiseFactory,
        RedisTagCache $cache,
        LoggerInterface $logger,
        ProposalCollectVoteRepository $proposalCollectVoteRepository,
        ProposalSelectionVoteRepository $proposalSelectionVoteRepository,
        string $cachePrefix,
        int $cacheTtl,
        bool $debug,
        GraphQLCollector $collector,
        bool $enableCache
    ) {
        $this->proposalCollectVoteRepository = $proposalCollectVoteRepository;
        $this->proposalSelectionVoteRepository = $proposalSelectionVoteRepository;
        parent::__construct(
            [$this, 'all'],
            $promiseFactory,
            $logger,
            $cache,
            $cachePrefix,
            $cacheTtl,
            $debug,
            $collector,
            $enableCache
        );
    }

    public function invalidate(AbstractStep $step): void
    {
        $this->cache->invalidateTags([$step->getId()]);
    }

    public function all(array $keys)
    {
        $connections = [];

        foreach ($keys as $key) {
            $connections[] = $this->resolve($key['step']);
        }

        return $this->getPromiseAdapter()->createAll($connections);
    }

    public function resolve(AbstractStep $step): int
    {
        if ($step instanceof CollectStep) {
            return $this->proposalCollectVoteRepository->countPublishedCollectVoteByStep($step);
        }

        if ($step instanceof SelectionStep) {
            return $this->proposalSelectionVoteRepository->countPublishedSelectionVoteByStep($step);
        }

        throw new \RuntimeException('Access denied');
    }

    protected function getCacheTag($key): array
    {
        return [$key['step']->getId()];
    }

    protected function serializeKey($key): array
    {
        return [
            'stepId' => $key['step']->getId(),
        ];
    }
}
