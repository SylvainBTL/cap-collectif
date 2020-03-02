<?php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Entity\Synthesis\Synthesis;
use Capco\AppBundle\Synthesis\Handler\SynthesisHandler;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SynthesisAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'enabled'
    ];

    public function prePersist($synthesis)
    {
        $this->createOrUpdateFromSource($synthesis);
    }

    public function preUpdate($synthesis)
    {
        $this->createOrUpdateFromSource($synthesis);
    }

    public function getBatchActions()
    {
        return [];
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $projectId = null;

        if ($this->hasParentFieldDescription()) {
            // this Admin is embedded
            $projectId = $this->getParentFieldDescription()
                ->getAdmin()
                ->getPersistentParameters()['projectId'];
        } else {
            $projectId = $this->getRequest()->get('projectId');
        }

        $formMapper
            ->add('enabled', null, [
                'label' => 'admin.fields.synthesis.enabled',
                'required' => false
            ])
            ->add('editable', null, [
                'label' => 'admin.fields.synthesis.editable',
                'required' => false
            ])
            ->add('sourceType', ChoiceFieldMaskType::class, [
                'label' => 'admin.fields.synthesis.source_type',
                'required' => true,
                'choices' => Synthesis::$sourceTypesLabels,
                'translation_domain' => 'CapcoAppBundle',
                'map' => [
                    'none' => ['enabled'],
                    'global.consultation' => ['enabled', 'consultationStep']
                ]
            ])
            ->add('consultationStep', EntityType::class, [
                'label' => 'global.consultation',
                'class' => ConsultationStep::class,
                'query_builder' => $this->createQueryBuilderForConsultationSteps($projectId),
                'required' => false,
                //                'empty_data' => 'global.none.feminine',
                'help' => 'admin.help.synthesis.consultation_step'
            ]);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        return $collection->clearExcept(['create', 'edit', 'delete']);
    }

    private function createOrUpdateFromSource($synthesis)
    {
        $this->getConfigurationPool()
            ->getContainer()
            ->get(SynthesisHandler::class)
            ->createOrUpdateElementsFromSource($synthesis);
    }

    private function createQueryBuilderForConsultationSteps($projectId)
    {
        $qb = $this->modelManager
            ->createQuery('CapcoAppBundle:Steps\ConsultationStep', 'cs')
            ->where('cs.isEnabled = :enabled')
            ->setParameter('enabled', true);
        if ($projectId) {
            $qb
                ->leftJoin('cs.projectAbstractStep', 'cas')
                ->andWhere('cas.project = :projectId')
                ->setParameter('projectId', $projectId);
        }

        return $qb;
    }
}
