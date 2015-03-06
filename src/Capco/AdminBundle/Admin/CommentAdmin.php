<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Capco\AppBundle\Entity\AbstractComment;

class CommentAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'updatedAt'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('Author', null, array(
                'label' => 'admin.fields.comment.author',
            ))
            ->add('authorName', null, array(
                'label' => 'admin.fields.comment.author_name',
            ))
            ->add('authorEmail', null, array(
                'label' => 'admin.fields.comment.author_email',
            ))
            ->add('voteCount', null, array(
                'label' => 'admin.fields.comment.vote_count',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.comment.updated_at',
            ))
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.comment.is_enabled',
            ))
            ->add('isTrashed', null, array(
                'label' => 'admin.fields.comment.is_trashed',
            ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('body', null, array(
                'label' => 'admin.fields.comment.body',
                'template' => 'CapcoAdminBundle:Comment:body_list_field.html.twig',
            ))
            ->add('object', null, array(
                'label' => 'admin.fields.comment.object',
                'template' => 'CapcoAdminBundle:Comment:object_list_field.html.twig',
                'mapped' => false,
            ))
            ->add('Author', 'sonata_type_model', array(
                'label' => 'admin.fields.comment.author',
                'template' => 'CapcoAdminBundle:Comment:author_list_field.html.twig',
                'mapped' => false,

            ))
            ->add('voteCount', null, array(
                'label' => 'admin.fields.comment.vote_count',
            ))
            ->add('isEnabled', null, array(
                'editable' => true,
                'label' => 'admin.fields.comment.is_enabled',
            ))
            ->add('isTrashed', null, array(
                'editable' => true,
                'label' => 'admin.fields.comment.is_trashed',
            ))
            ->add('updatedAt', 'datetime', array(
                'label' => 'admin.fields.comment.updated_at',
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $subject = $this->getSubject();

        $showMapper
            ->add('body', null, array(
                'label' => 'admin.fields.comment.body',
            ))
            ->add('object', null, array(
                'label' => 'admin.fields.comment.object',
                'template' => 'CapcoAdminBundle:Comment:object_show_field.html.twig',
                'mapped' => false,
            ))
        ;

        if (null != $subject->getAuthor()) {
            $showMapper
                ->add('Author', null, array(
                    'label' => 'admin.fields.comment.author',
                ))
            ;
        } else {
            $showMapper
                ->add('authorName', null, array(
                    'label' => 'admin.fields.comment.author_name',
                ))
                ->add('authorEmail', null, array(
                    'label' => 'admin.fields.comment.author_email',
                ))
                ->add('authorIp', null, array(
                    'label' => 'admin.fields.comment.author_ip',
                ))
            ;
        }


        $showMapper
            ->add('voteCount', null, array(
                'label' => 'admin.fields.comment.vote_count',
            ))
            ->add('createdAt', null, array(
                'label' => 'admin.fields.comment.created_at',
            ))
            ->add('updatedAt', null, array(
                'label' => 'admin.fields.comment.updated_at',
            ))
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.comment.is_enabled',
            ))
            ->add('isTrashed', null, array(
                'label' => 'admin.fields.comment.is_trashed',
            ))
        ;

        if ($subject->getIsTrashed()) {
            $showMapper
                ->add('trashedAt', null, array(
                    'label' => 'admin.fields.comment.trashed_at',
                ))
                ->add('trashedReason', null, array(
                    'label' => 'admin.fields.comment.trashed_reason',
                ))
            ;
        }

    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->add('body', null, array(
                'label' => 'admin.fields.comment.body',
                'attr' => array('rows' => 8)
            ))
            ->add('Author', null, array(
                'label' => 'admin.fields.comment.author',
                'help' => 'admin.help.comment.author'
            ))
            ->add('authorName', null, array(
                'label' => 'admin.fields.comment.author_name',
            ))
            ->add('authorEmail', null, array(
                'label' => 'admin.fields.comment.author_email',
            ))
            ->add('isEnabled', null, array(
                'label' => 'admin.fields.comment.is_enabled',
                'required' => false,
            ))
            ->add('isTrashed', null, array(
                'label' => 'admin.fields.comment.is_trashed',
                'required' => false,
            ))
            ->add('trashedReason', null, array(
                'label' => 'admin.fields.comment.trashed_reason',
            ))
        ;

    }
}
