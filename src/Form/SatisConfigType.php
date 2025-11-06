<?php

namespace App\Form;

use App\DTO\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SatisConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex('#[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*#'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('homepage', UrlType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('require', CollectionType::class, [
                'entry_type'   => PackageConstraintType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label'        => false,
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('archive', ArchiveType::class, [
                'required'     => false,
                'label'        => false,
            ])
            ->add('blacklist', CollectionType::class, [
                'entry_type'   => PackageConstraintType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label'        => false,
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('abandoned', CollectionType::class, [
                'entry_type'   => AbandonedType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label'        => false,
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('requireAll', CheckboxType::class, [
                'required' => false,
            ])
            ->add('requireDependencies', CheckboxType::class, [
                'required' => false,
            ])
            ->add('requireDevDependencies', CheckboxType::class, [
                'required' => false,
            ])
            ->add('requireDependencyFilter', CheckboxType::class, [
                'required' => false,
            ])
            ->add('repositories', CollectionType::class, [
                'entry_type'   => RepositoryType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label'        => false,
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('minimumStability', ChoiceType::class, [
                'required'    => false,
                'choices'     => PackageStabilityType::STABILITY_LEVELS,
                'constraints' => [
                    new Assert\Choice(['choices' => PackageStabilityType::STABILITY_LEVELS]),
                ],
            ])
            ->add('minimumStabilityPerPackage', CollectionType::class, [
                'entry_type'   => PackageStabilityType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label'        => false,
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('includeFilename', TextType::class, [
                'required' => false,
                'attr'     => [
                    'rel'        => 'tooltip',
                    'data-title' => <<<END
Specify filename instead of default include/all\${SHA1_HASH}.json
END,
                ],
            ])
            ->add('outputDir', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'empty_data' => Configuration::DEFAULT_OUTPUT_DIR,
            ])
            ->add('outputHtml', CheckboxType::class, [
                'required' => false,
                'label'    => 'Output HTML',
            ])
            ->add('providers', CheckboxType::class, [
                'required' => false,
                'attr'     => [
                    'rel'        => 'tooltip',
                    'data-title' => 'If enabled, dump package providers',
                ],
            ])
            ->add('config', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
                'trim'       => true,
            ])
            ->add('twigTemplate', TextType::class, [
                'required' => false,
            ])
            ->add('notifyBatch', UrlType::class, [
                'label'    => 'Notify batch URL',
                'required' => false,
            ])
            ->add('prettyPrint', CheckboxType::class, [
                'label'    => 'Pretty print',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
        ]);
    }
}
