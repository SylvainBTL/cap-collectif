<?php

namespace Capco\AppBundle\Form;

use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Enum\ProjectVisibilityMode;
use Capco\AppBundle\Repository\ProjectRepository;
use Capco\AppBundle\Repository\ThemeRepository;
use Capco\AppBundle\Toggle\Manager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostSearchType extends AbstractType
{
    private $toggleManager;

    public function __construct(Manager $toggleManager)
    {
        $this->toggleManager = $toggleManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->toggleManager->isActive('themes')) {
            $builder->add('theme', EntityType::class, [
                'required' => false,
                'class' => Theme::class,
                'choice_label' => 'translate.title',
                'label' => 'global.theme',
                'translation_domain' => 'CapcoAppBundle',
                'query_builder' => function (ThemeRepository $tr) {
                    return $tr
                        ->createQueryBuilder('t')
                        ->where('t.isEnabled = :enabled')
                        ->setParameter('enabled', true);
                },
                'placeholder' => 'global.select_themes',
                'attr' => ['onchange' => 'this.form.submit()'],
            ]);
        }

        $builder->add('project', EntityType::class, [
            'required' => false,
            'class' => 'CapcoAppBundle:Project',
            'choice_label' => 'title',
            'label' => 'global.participative.project.label',
            'translation_domain' => 'CapcoAppBundle',
            'query_builder' => function (ProjectRepository $cr) {
                return $cr
                    ->createQueryBuilder('c')
                    ->join('c.posts', 'p')
                    ->where('c.visibility = :visibility')
                    ->andWhere('p.displayedOnBlog = true')
                    ->setParameter('visibility', ProjectVisibilityMode::VISIBILITY_PUBLIC)
                    ;
            },
            'placeholder' => 'global.all.projects',
            'attr' => ['onchange' => 'this.form.submit()'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}
