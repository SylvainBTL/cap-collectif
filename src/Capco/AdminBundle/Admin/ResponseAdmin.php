<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ResponseAdmin extends CapcoAdmin
{
    protected $formOptions = [
        'cascade_validation' => true,
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('question', null, [
                'label' => 'admin.fields.response.question',
                'required' => false,
                'attr' => ['readonly' => true, 'disabled' => true],
            ])
            ->add('value', null, [
                'label' => 'admin.fields.response.value',
                'required' => false,
                'attr' => ['readonly' => true],
            ]);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['edit', 'delete', 'show']);
    }
}
