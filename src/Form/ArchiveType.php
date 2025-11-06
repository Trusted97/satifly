<?php

namespace App\Form;

use App\DTO\Archive;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArchiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('directory', TextType::class, [
                'label'    => 'Archive Directory',
                'help'     => 'Relative path where archives will be stored.',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'e.g., dist/',
                ],
            ])
            ->add('format', TextType::class, [
                'label'    => 'Archive Format',
                'help'     => 'Archive format (e.g. zip, tar, tar.gz).',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'zip',
                ],
            ])
            ->add('absoluteDirectory', TextType::class, [
                'label'    => 'Absolute Directory (optional)',
                'help'     => 'Use an absolute path to override the relative directory.',
                'required' => false,
            ])
            ->add('prefixUrl', TextType::class, [
                'label'    => 'Prefix URL',
                'help'     => 'Base URL to use when generating archive download links.',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'https://example.com/dist/',
                ],
            ])
            ->add('skipDev', CheckboxType::class, [
                'label'    => 'Skip Development Packages',
                'required' => false,
                'help'     => 'Exclude packages with “dev” stability from archives.',
            ])
            ->add('checksum', CheckboxType::class, [
                'label'    => 'Generate Checksums',
                'required' => false,
                'help'     => 'Create SHA1 checksum files for each generated archive.',
            ])
            ->add('ignoreFilters', CheckboxType::class, [
                'label'    => 'Ignore Filters',
                'required' => false,
                'help'     => 'Ignore “require” or “blacklist” filters when generating archives.',
            ])
            ->add('overrideDistType', CheckboxType::class, [
                'label'    => 'Override Distribution Type',
                'required' => false,
                'help'     => 'Force the “dist” type even if the package specifies otherwise.',
            ])
            ->add('rearchive', CheckboxType::class, [
                'label'    => 'Rearchive Existing Files',
                'required' => false,
                'help'     => 'Rebuild archives even if they already exist.',
            ])
            ->add('whitelist', CollectionType::class, [
                'entry_type'   => TextType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'required'     => false,
                'label'        => false,
                'attr'         => [
                    'data-title' => 'Whitelist of packages always included in archives',
                ],
            ])
            ->add('blacklist', CollectionType::class, [
                'entry_type'   => TextType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'required'     => false,
                'label'        => false,
                'attr'         => [
                    'data-title' => 'Blacklist of packages excluded from archives',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Archive::class,
        ]);
    }
}
