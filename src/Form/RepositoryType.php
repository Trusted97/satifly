<?php

namespace App\Form;

use App\DTO\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RepositoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [
            'artifact',
            'composer',
            'git',
            'github',
            'gitlab',
            'git-bitbucket',
            'hg',
            'hg-bitbucket',
            'package',
            'path',
            'pear',
            'perforce',
            'svn',
            'vcs',
        ];

        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required'    => true,
                    'empty_data'  => '',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'attr' => [
                        'placeholder' => 'Repository name ( vendor/vendor-name )',
                    ],
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'required'    => true,
                    'empty_data'  => '',
                    'choices'     => \array_combine($types, $types),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Choice(['choices' => $types]),
                    ],
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'required'    => true,
                    'empty_data'  => '',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'attr' => [
                        'placeholder' => 'Repository url',
                    ],
                ]
            )
            ->add(
                'installationSource',
                ChoiceType::class,
                [
                    'required'    => false,
                    'placeholder' => false,
                    'choices'     => [
                        'dist'   => 'dist',
                        'source' => 'source',
                    ],
                ]
            );

        if ($options['show_full_update']) {
            $builder->add('fullUpdate', CheckboxType::class, [
                'required' => false,
                'label'    => 'Performs a complete sync of all data. This process can take a few minutes.',
                'mapped'   => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'       => Repository::class,
                'empty_data'       => new Repository(url: '', type: 'vcs', name: ''),
                'show_full_update' => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'RepositoryType';
    }
}
