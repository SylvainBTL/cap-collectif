<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Enum\ProjectVisibilityMode;
use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Traits\ProjectVisibilityTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Project[]|null findAll()
 */
class ProjectRepository extends EntityRepository
{
    use ProjectVisibilityTrait;

    public function findAllIdsWithSlugs(): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->select('p.id', 'p.slug')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllWithSteps()
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->addSelect('pas', 'step', 'status', 'theme')
            ->leftJoin('p.steps', 'pas')
            ->leftJoin('pas.step', 'step')
            ->leftJoin('step.statuses', 'status')
            ->leftJoin('p.themes', 'theme')
            ->getQuery()
            ->getResult();
    }

    public function hydrateFromIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->addSelect('theme', 'cover', 'authors', 'district', 'pas', 'step')
            ->leftJoin('p.themes', 'theme', 'WITH', 'theme.isEnabled = true')
            ->leftJoin('p.districts', 'district')
            ->leftJoin('p.Cover', 'cover')
            ->leftJoin('p.authors', 'authors')
            ->leftJoin('p.steps', 'pas')
            ->leftJoin('pas.step', 'step')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneWithoutVisibility(string $slug): ?Project
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('theme', 'cover', 'authors', 'district', 'pas', 'step')
            ->leftJoin('p.themes', 'theme', 'WITH', 'theme.isEnabled = true')
            ->leftJoin('p.districts', 'district')
            ->leftJoin('p.Cover', 'cover')
            ->leftJoin('p.authors', 'authors')
            ->leftJoin('p.steps', 'pas')
            ->leftJoin('pas.step', 'step')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug);

        return $qb
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 60)
            ->getOneOrNullResult();
    }

    public function getByUser(User $user, $viewer = null)
    {
        $qb = $this->getProjectsViewerCanSeeQueryBuilder($viewer)
            ->addSelect('a', 'm', 't')
            ->leftJoin('a.media', 'm')
            ->leftJoin('p.projectType', 't')
            ->leftJoin('p.authors', 'authors')
            ->andWhere('p.authors = :user')
            ->setParameter('user', $user)
            ->orderBy('p.updatedAt', 'DESC');

        return $qb->getQuery()->execute();
    }

    public function getAuthorsId($viewer = null, $order = 'DESC'): array
    {
        $qb = $this->getProjectsViewerCanSeeQueryBuilder($viewer)
            ->select('a.id')
            ->leftJoin('p.authors', 'a')
            ->groupBy('a.id')
            ->orderBy('a.createdAt', $order);

        return $qb->getQuery()->execute();
    }

    public function getSearchResults(
        int $nbByPage = 8,
        int $page = 1,
        $theme = null,
        $sort = null,
        $term = null,
        $type = null,
        $viewer = null
    ): Paginator {
        if ($page < 1) {
            throw new \InvalidArgumentException(
                sprintf('The argument "page" cannot be lower than 1 (current value: "%s")', $page)
            );
        }

        $qb = $this->getProjectsViewerCanSeeQueryBuilder($viewer);

        $qb
            ->addSelect('t', 'pas', 's', 'pov')
            ->leftJoin('p.themes', 't')
            ->leftJoin('p.steps', 'pas')
            ->leftJoin('pas.step', 's')
            ->leftJoin('p.Cover', 'pov')
            ->leftJoin('p.projectType', 'projectType')
            ->addOrderBy('p.publishedAt', 'DESC');

        if (null !== $theme && Theme::FILTER_ALL !== $theme) {
            $qb->andWhere('t.slug = :theme')->setParameter('theme', $theme);
        }

        if (null !== $term) {
            $qb->andWhere('p.title LIKE :term')->setParameter('term', '%' . $term . '%');
        }

        if (null !== $type) {
            $qb->andWhere('projectType.slug = :type')->setParameter('type', $type);
        }

        if (
            isset(Project::$sortOrder[$sort]) &&
            Project::SORT_ORDER_CONTRIBUTIONS_COUNT === Project::$sortOrder[$sort]
        ) {
            $qb->orderBy('p.contributionsCount', 'DESC');
        } else {
            $qb->orderBy('p.publishedAt', 'DESC');
        }

        $query = $qb->getQuery();

        if ($nbByPage > 0) {
            $query->setFirstResult(($page - 1) * $nbByPage)->setMaxResults($nbByPage);
        }

        return new Paginator($query);
    }

    public function getLastPublished($limit = 1, $offset = 0, $viewer = null): array
    {
        $qb = $this->getProjectsViewerCanSeeQueryBuilder($viewer)
            ->addSelect('t', 'pas', 's', 'pov', 'pvg')
            ->leftJoin('p.themes', 't')
            ->leftJoin('p.steps', 'pas')
            ->leftJoin('pas.step', 's')
            ->leftJoin('p.Cover', 'pov')
            ->leftJoin('p.projectType', 'type')
            ->addOrderBy('p.publishedAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        $results = new Paginator($qb, ($fetchJoin = true));
        $projects = [];
        foreach ($results as $project) {
            $projects[] = $project;
        }

        return $projects;
    }

    public function getProjectsByTheme(Theme $theme, $viewer = null): array
    {
        $query = $this->getProjectsViewerCanSeeQueryBuilder($viewer)
            ->addSelect('t', 'pas', 's', 'pov')
            ->leftJoin('p.themes', 't')
            ->leftJoin('p.steps', 'pas')
            ->leftJoin('pas.step', 's')
            ->leftJoin('p.Cover', 'pov')
            ->leftJoin('p.projectType', 'type')
            ->andWhere('t = :theme')
            ->setParameter('theme', $theme)
            ->addOrderBy('p.publishedAt', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function countPublished($viewer = null)
    {
        $qb = $this->getProjectsViewerCanSeeQueryBuilder($viewer)->select('COUNT(p.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getProjectsViewerCanSeeQueryBuilder($viewer = null): QueryBuilder
    {
        $visibility = $this->getVisibilityForViewer($viewer);

        $qb = $this->createQueryBuilder('p')
            ->addSelect('authors')
            ->leftJoin('p.authors', 'authors')
            ->leftJoin('p.restrictedViewerGroups', 'pvg')
            ->orWhere('p.visibility IN (:visibility)')
            ->setParameter('visibility', $visibility);
        // https://github.com/cap-collectif/platform/pull/5877#discussion_r213009730
        /** @var User $viewer */
        $viewerGroups = $viewer && \is_object($viewer) ? $viewer->getUserGroupIds() : [];

        if ($viewer && \is_object($viewer) && !$viewer->isSuperAdmin()) {
            if ($viewerGroups) {
                $qb->orWhere(
                    $qb
                        ->expr()
                        ->andX(
                            $qb->expr()->eq('p.visibility', ':custom'),
                            $qb->expr()->in('pvg.id', ':pvgId')
                        )
                );
                $qb->setParameter('custom', ProjectVisibilityMode::VISIBILITY_CUSTOM);
                $qb->setParameter('pvgId', $viewerGroups);
            }
            $qb->orWhere(
                $qb
                    ->expr()
                    ->andX(
                        $qb->expr()->eq('p.visibility', ':me'),
                        $qb->expr()->eq('authors.user', ':viewer')
                    )
            );
            $qb->setParameter('me', ProjectVisibilityMode::VISIBILITY_ME);
            $qb->setParameter('viewer', $viewer);
        }

        return $qb;
    }
}
