<?php

namespace Capco\AppBundle\GraphQL\DataLoader\User;

use Capco\AppBundle\Repository\ProposalCollectSmsVoteRepository;
use Capco\AppBundle\Repository\ProposalSelectionSmsVoteRepository;
use DeepCopy\DeepCopy;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Psr\Log\LoggerInterface;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Cache\RedisTagCache;
use Symfony\Component\Stopwatch\Stopwatch;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\GraphQL\ConnectionBuilder;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Overblog\GraphQLBundle\Definition\Argument;
use Capco\AppBundle\DataCollector\GraphQLCollector;
use Overblog\PromiseAdapter\PromiseAdapterInterface;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\AppBundle\Repository\AbstractStepRepository;
use Capco\AppBundle\Repository\AbstractVoteRepository;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Capco\AppBundle\GraphQL\DataLoader\BatchDataLoader;
use Capco\AppBundle\Resolver\ProposalStepVotesResolver;
use Capco\AppBundle\Repository\ProposalCollectVoteRepository;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Capco\AppBundle\Repository\ProposalSelectionVoteRepository;

class ViewerProposalVotesDataLoader extends BatchDataLoader
{
    public bool $enableBatch = true;
    public bool $useElasticsearch = true;
    private AbstractStepRepository $abstractStepRepository;
    private ProposalCollectVoteRepository $proposalCollectVoteRepository;
    private ProposalSelectionVoteRepository $proposalSelectionVoteRepository;
    private GlobalIdResolver $globalIdResolver;
    private ProposalStepVotesResolver $helper;
    private AbstractVoteRepository $abstractVoteRepository;
    private ProposalCollectSmsVoteRepository $proposalCollectSmsVoteRepository;
    private ProposalSelectionSmsVoteRepository $proposalSelectionSmsVoteRepository;

    public function __construct(
        PromiseAdapterInterface $promiseFactory,
        RedisTagCache $cache,
        LoggerInterface $logger,
        AbstractStepRepository $repository,
        ProposalCollectVoteRepository $proposalCollectVoteRepository,
        ProposalSelectionVoteRepository $proposalSelectionVoteRepository,
        ProposalCollectSmsVoteRepository $proposalCollectSmsVoteRepository,
        ProposalSelectionSmsVoteRepository $proposalSelectionSmsVoteRepository,
        ProposalStepVotesResolver $helper,
        GlobalIdResolver $globalIdResolver,
        string $cachePrefix,
        int $cacheTtl,
        bool $debug,
        GraphQLCollector $collector,
        Stopwatch $stopwatch,
        bool $enableCache,
        AbstractVoteRepository $abstractVoteRepository
    ) {
        $this->abstractStepRepository = $repository;
        $this->proposalCollectVoteRepository = $proposalCollectVoteRepository;
        $this->globalIdResolver = $globalIdResolver;
        $this->proposalSelectionVoteRepository = $proposalSelectionVoteRepository;
        $this->abstractVoteRepository = $abstractVoteRepository;
        $this->helper = $helper;
        $this->proposalCollectSmsVoteRepository = $proposalCollectSmsVoteRepository;
        $this->proposalSelectionSmsVoteRepository = $proposalSelectionSmsVoteRepository;

        parent::__construct(
            [$this, 'all'],
            $promiseFactory,
            $logger,
            $cache,
            $cachePrefix,
            $cacheTtl,
            $debug,
            $collector,
            $stopwatch,
            $enableCache
        );
    }

    public function invalidate(User $user): void
    {
        $this->cache->invalidateTags([$user->getId()]);
    }

    public function all(array $keys)
    {
        if ($this->debug) {
            $this->logger->info(
                __METHOD__ .
                    'called for keys : ' .
                    var_export(
                        array_map(function ($key) {
                            return $this->serializeKey($key);
                        }, $keys),
                        true
                    )
            );
        }

        $connections = [];
        foreach ($keys as $key) {
            $connections[] = $this->resolve($key['user'], $key['args']);
        }

        return $this->getPromiseAdapter()->createAll($connections);
    }

    public function getConnectionForStepAndUser(
        AbstractStep $step,
        User $user,
        Argument $args
    ): Connection {
        $field = $args->offsetGet('orderBy')['field'];
        $direction = $args->offsetGet('orderBy')['direction'];

        if ($step instanceof CollectStep) {
            $paginator = new Paginator(function (int $offset, int $limit) use (
                $user,
                $step,
                $field,
                $direction
            ) {
                return $this->proposalCollectVoteRepository
                    ->getByAuthorAndStep($user, $step, $limit, $offset, $field, $direction)
                    ->getIterator()
                    ->getArrayCopy();
            });
            $totalCount = $this->proposalCollectVoteRepository->countByAuthorAndStep($user, $step);
        } elseif ($step instanceof SelectionStep) {
            $paginator = new Paginator(function (int $offset, int $limit) use (
                $user,
                $step,
                $field,
                $direction
            ) {
                return $this->proposalSelectionVoteRepository
                    ->getByAuthorAndStep($user, $step, $limit, $offset, $field, $direction)
                    ->getIterator()
                    ->getArrayCopy();
            });
            $totalCount = $this->proposalSelectionVoteRepository->countByAuthorAndStep(
                $user,
                $step
            );
        } else {
            throw new \RuntimeException('Expected a proposal step got :' . \get_class($step));
        }
        $connection = $paginator->auto($args, $totalCount);

        $creditsSpent = $this->helper->getSpentCreditsForUser($user, $step);
        $connection->{'creditsSpent'} = $creditsSpent;
        $connection->{'creditsLeft'} = $step->getBudget() - $creditsSpent;

        return $connection;
    }

    public function getConnectionForStepAndAnonUser(AbstractStep $step, Argument $args): ConnectionInterface
    {
        $field = $args->offsetGet('orderBy')['field'];
        $direction = $args->offsetGet('orderBy')['direction'];
        $token = $args->offsetGet('token');

        if ($step instanceof CollectStep) {
            $paginator = new Paginator(function (int $offset, int $limit) use (
                $step,
                $field,
                $direction,
                $token
            ) {

                return $this->proposalCollectSmsVoteRepository
                    ->getByTokenAndStep($step, $token, $limit, $offset, $field, $direction)
                    ->getIterator()
                    ->getArrayCopy();
            });
            $totalCount = $this->proposalCollectSmsVoteRepository->countByTokenAndStep($step, $token);
        } elseif ($step instanceof SelectionStep) {
            $paginator = new Paginator(function (int $offset, int $limit) use (
                $step,
                $field,
                $direction,
                $token
            ) {
                return $this->proposalSelectionSmsVoteRepository
                    ->getByTokenAndStep($step, $token, $limit, $offset, $field, $direction)
                    ->getIterator()
                    ->getArrayCopy();
            });
            $totalCount = $this->proposalSelectionSmsVoteRepository->countByTokenAndStep(
                $step,
                $token
            );
        } else {
            throw new \RuntimeException('Expected a proposal step got :' . \get_class($step));
        }
        $connection = $paginator->auto($args, $totalCount);

        return $connection;
    }

    protected function normalizeValue($value)
    {
        $copier = new DeepCopy(true);

        $connection = $copier->copy($value);

        if ($connection) {
            foreach ($connection->getEdges() as $vote) {
                $vote->setNode($vote->getNode()->getId());
            }
        }

        return $connection;
    }

    protected function denormalizeValue($value)
    {
        if ($value) {
            foreach ($value->getEdges() as $vote) {
                $vote->setNode($this->abstractVoteRepository->find($vote->getNode()));
            }
        }

        return $value;
    }

    protected function getCacheTag($key): array
    {
        return [$key['user']->getId()];
    }

    protected function serializeKey($key)
    {
        return [
            'userId' => $key['user'] ? $key['user']->getId() : null,
            'args' => $key['args']->getArrayCopy(),
        ];
    }

    private function resolve(?User $user, Argument $args): ConnectionInterface
    {
        try {
            if (!$user && !$args->offsetGet('token')) return ConnectionBuilder::empty();

            $step = $this->globalIdResolver->resolve($args->offsetGet('stepId'), $user);

            if (!$step) {
                return ConnectionBuilder::empty();
            }

            if (!$user && $args->offsetGet('token')) {
                return $this->getConnectionForStepAndAnonUser($step, $args);
            }

            return $this->getConnectionForStepAndUser($step, $user, $args);
        } catch (\RuntimeException $exception) {
            $this->logger->error(__METHOD__ . ' : ' . $exception->getMessage());

            throw new \RuntimeException($exception->getMessage());
        }
    }
}
