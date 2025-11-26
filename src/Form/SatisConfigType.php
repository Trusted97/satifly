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
                'help'        => 'Package name in vendor/name format.',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex('#[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*#'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
                'help'       => 'Short description of the Satis repository.',
            ])
            ->add('homepage', UrlType::class, [
                'required'    => true,
                'help'        => 'Homepage URL for this Satis instance.',
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
                'required' => false,
                'label'    => false,
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
                'help'     => 'Include all packages from all repositories.',
            ])
            ->add('requireDependencies', CheckboxType::class, [
                'required' => false,
                'help'     => 'Automatically include required dependencies.',
            ])
            ->add('requireDevDependencies', CheckboxType::class, [
                'required' => false,
                'help'     => 'Include development dependencies.',
            ])
            ->add('requireDependencyFilter', CheckboxType::class, [
                'required' => false,
                'help'     => 'Exclude packages not explicitly required.',
            ])
            ->add('repositories', CollectionType::class, [
                'entry_type'   => RepositoryType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype'    => true,
                'label'        => false,
                'help'         => 'List of Composer repositories used as sources.',
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('minimumStability', ChoiceType::class, [
                'required'    => false,
                'choices'     => PackageStabilityType::STABILITY_LEVELS,
                'help'        => 'Minimum stability level allowed for packages.',
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
                'help'     => 'Optional custom filename for the include file.',
                'attr'     => [
                    'rel'        => 'tooltip',
                    'data-title' => <<<END
Specify filename instead of default include/all\${SHA1_HASH}.json
END,
                ],
            ])
            ->add('outputDir', TextType::class, [
                'required'    => true,
                'help'        => 'Directory where Satis will write generated files.',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'empty_data' => Configuration::DEFAULT_OUTPUT_DIR,
            ])
            ->add('outputHtml', CheckboxType::class, [
                'required' => false,
                'label'    => 'Output HTML',
                'help'     => 'Generate the static HTML front-end.',
            ])
            ->add('providers', CheckboxType::class, [
                'required' => false,
                'help'     => 'If enabled, dump package provider metadata.',
                'attr'     => [
                    'rel'        => 'tooltip',
                    'data-title' => 'If enabled, dump package providers',
                ],
            ])
            ->add('config', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
                'trim'       => true,
                'help'       => 'Additional Composer configuration (JSON).',
            ])
            ->add('twigTemplate', TextType::class, [
                'required' => false,
                'help'     => 'Custom Twig template for the HTML output.',
            ])
            ->add('notifyBatch', UrlType::class, [
                'label'    => 'Notify batch URL',
                'required' => false,
                'help'     => 'URL used for Composer notification batches.',
            ])
            ->add('prettyPrint', CheckboxType::class, [
                'label'    => 'Pretty print',
                'required' => false,
                'help'     => 'Pretty-print JSON output.',
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
