<?php
namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\QueryBuilder;

class ProposalAdmin extends AbstractAdmin
{
    protected $datagridValues = ['_sort_order' => 'DESC', '_sort_by' => 'createdAt'];
    private $tokenStorage;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($code, $class, $baseControllerName);
        $this->tokenStorage = $tokenStorage;
    }

    public function getList()
    {
        // Remove APC Cache for soft delete
        $em = $this->getConfigurationPool()
            ->getContainer()
            ->get('doctrine')
            ->getManager();
        $em
            ->getConfiguration()
            ->getResultCacheImpl()
            ->deleteAll();

        return parent::getList();
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('fullReference', null, ['label' => 'admin.fields.proposal.reference'])
            ->add('titleInfo', null, [
                'label' => 'admin.fields.proposal.title',
                'template' => 'CapcoAdminBundle:Proposal:title_list_field.html.twig',
            ])
            ->add('author', 'sonata_type_model', [
                'label' => 'admin.fields.proposal.author',
                'template' => 'CapcoAdminBundle:common:author_list_field.html.twig',
            ])
            ->add('project', 'sonata_type_model', [
                'label' => 'admin.fields.proposal.project',
                'template' => 'CapcoAdminBundle:Proposal:project_list_field.html.twig',
            ])
            ->add('category', 'sonata_type_model', ['label' => 'admin.fields.proposal.category'])
            ->add('district', 'sonata_type_model', ['label' => 'admin.fields.proposal.district'])
            ->add('lastStatus', null, [
                'label' => 'admin.fields.proposal.status',
                'template' => 'CapcoAdminBundle:Proposal:last_status_list_field.html.twig',
            ])
            ->add('state', null, [
                'label' => 'admin.fields.proposal.state.label',
                'template' => 'CapcoAdminBundle:Proposal:state_list_field.html.twig',
            ])
            ->add('evaluers', null, ['label' => 'admin.fields.proposal.evaluers'])
            ->addIdentifier('createdAt', null, ['label' => 'admin.fields.proposal.created_at'])
            ->add('updatedInfo', 'datetime', [
                'label' => 'admin.fields.proposal.updated',
                'template' => 'CapcoAdminBundle:common:updated_info_list_field.html.twig',
            ]);
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $currentUser = $this->getConfigurationPool()
            ->getContainer()
            ->get('security.token_storage')
            ->getToken()
            ->getUser();

        $datagridMapper
            ->add('title', null, ['label' => 'admin.fields.proposal.title'])
            ->add('reference', null, ['label' => 'admin.fields.proposal.reference_of_proposal'])
            ->add('enabled', null, ['label' => 'admin.fields.proposal.enabled'])
            ->add('createdAt', null, ['label' => 'admin.fields.proposal.created_at'])
            ->add('trashedStatus', null, ['label' => 'admin.fields.proposal.is_trashed'])
            ->add('draft', null, ['label' => 'admin.fields.proposal.draft'])
            ->add(
                'updateAuthor',
                'doctrine_orm_model_autocomplete',
                ['label' => 'admin.fields.proposal.updateAuthor'],
                null,
                ['property' => 'username']
            )
            ->add('district', null, ['label' => 'admin.fields.proposal.district'])
            ->add(
                'author',
                'doctrine_orm_model_autocomplete',
                ['label' => 'admin.fields.proposal.author'],
                null,
                ['property' => 'username']
            )
            ->add(
                'likers',
                'doctrine_orm_model_autocomplete',
                ['label' => 'admin.fields.proposal.likers'],
                null,
                ['property' => 'username']
            )
            ->add('updatedAt', null, ['label' => 'admin.fields.proposal.updated_at']);
        if ($currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $datagridMapper->add('deletedAt', null, ['label' => 'admin.fields.proposal.deleted']);
        }
        $datagridMapper
            ->add('status', null, ['label' => 'admin.fields.proposal.status'])
            ->add('estimation', null, ['label' => 'admin.fields.proposal.estimation'])
            ->add('proposalForm.step.projectAbstractStep.project', null, [
                'label' => 'admin.fields.proposal.project',
            ])
            ->add('expired', null, ['label' => 'admin.global.expired'])
            ->add('evaluers', null, ['label' => 'admin.global.evaluers']);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['batch', 'list', 'edit']);
    }

    /**
     * if user is supper admin return all else return only what I can see
     */
    public function createQuery($context = 'list')
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            return parent::createQuery($context);
        }

        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        $query
            ->leftJoin($query->getRootAliases()[0] . '.proposalForm', 'pF')
            ->leftJoin('pF.step', 's')
            ->leftJoin('s.projectAbstractStep', 'pAs')
            ->leftJoin('pAs.project', 'p')
            ->andWhere(
                $query
                    ->expr()
                    ->andX(
                        $query->expr()->eq('p.Author', ':author'),
                        $query->expr()->eq('p.visibility', 0)
                    )
            );
        $query->orWhere($query->expr()->gte('p.visibility', 1));
        $query->setParameter('author', $user);

        return $query;
    }
}
