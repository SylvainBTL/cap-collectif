<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Capco\AppBundle\Entity\SocialNetwork;
use Sonata\AdminBundle\Route\RouteCollection;

class SocialNetworkAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'title'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array(
                'label' => 'admin.fields.social_network.title',
            ))
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.social_network.is_enabled',
            ))
            ->add('link', null, array(
                'label' => 'admin.fields.social_network.link',
            ))
            ->add('position', null, array(
                'label' => 'admin.fields.social_network.position',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.social_network.updated_at',
            ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array(
                'label' => 'admin.fields.social_network.title',
            ))
            ->add('isEnabled', null, array(
                'editable' => true,
                'label' => 'admin.fields.social_network.is_enabled',
            ))
            ->add('link', null, array(
                'label' => 'admin.fields.social_network.link',
            ))
            ->add('media', 'sonata_media_type', array(
                'template' => 'CapcoAdminBundle:SocialNetwork:media_list_field.html.twig',
                'provider' => 'sonata.media.provider.image',
                'label' => 'admin.fields.social_network.media',
            ))
            ->add('position', null, array(
                'label' => 'admin.fields.social_network.position',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.social_network.updated_at',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
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
                'label' => 'admin.fields.social_network.title',
            ))
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.social_network.is_enabled',
                'required' => false,
            ))
            ->add('link', null, array(
                'label' => 'admin.fields.social_network.link',
            ))
            ->add('position', null, array(
                'label' => 'admin.fields.social_network.position',
            ))
            ->add('media', 'sonata_media_type', array(
                'provider' => 'sonata.media.provider.image',
                'context' => 'default',
                'label' => 'admin.fields.social_network.media',
            ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, array(
                'label' => 'admin.fields.social_network.title',
            ))
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.social_network.is_enabled',
            ))
            ->add('link', null, array(
                'label' => 'admin.fields.social_network.link',
            ))
            ->add('media', 'sonata_media_type', array(
                'template' => 'CapcoAdminBundle:SocialNetwork:media_show_field.html.twig',
                'label' => 'admin.fields.social_network.media',
            ))
            ->add('position', null, array(
                'label' => 'admin.fields.social_network.position',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.social_network.updated_at',
            ))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
    }
}
