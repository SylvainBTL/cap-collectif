<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ConsultationTypeAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array(
                'label' => 'admin.fields.consultation_type.title',
            ))
            ->add('opinionTypes', null, array(
                'label' => 'admin.fields.consultation_type.opinion_types',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.consultation_type.updated_at',
            ))
            ->add('createdAt', null, array(
                'label' => 'admin.fields.consultation_type.created_at',
            ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->addIdentifier('title', null, array(
                'label' => 'admin.fields.consultation_type.title',
            ))
            ->add('opinionTypes', 'sonata_type_model', array(
                'label' => 'admin.fields.consultation_type.opinion_types',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.consultation_type.updated_at',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, array(
                'label' => 'admin.fields.consultation_type.title',
            ))
            ->add('opinionTypes', 'sonata_type_model', array(
                'label' => 'admin.fields.consultation_type.opinion_types',
                'by_reference' => false,
                'multiple' => true,
                'expanded' => false,
                'query' => $this->createQueryBuilderForOpinionTypes(),
            ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $subject = $this->getSubject();

        $showMapper
            ->add('title', null, array(
                'label' => 'admin.fields.consultation_type.title',
            ))
            ->add('opinionTypes', 'sonata_type_model', array(
                'label' => 'admin.fields.consultation_type.opinion_types',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.consultation_type.updated_at',
            ))
            ->add('createdAt', null, array(
                'label' => 'admin.fields.consultation_type.created_at',
            ))
        ;
    }

    private function createQueryBuilderForOpinionTypes()
    {
         return $this->getConfigurationPool()
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('CapcoAppBundle:OpinionType')
            ->getRootNodesQuery('position', 'ASC')
        ;
    }
}
