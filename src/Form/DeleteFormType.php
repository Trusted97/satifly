<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DeleteFormType
 */
class DeleteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('entity');

        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => null,
        ]);

        $resolver->setNormalizer('csrf_token_id', function ($options) {
            $entity = $options['entity'];

            if (\method_exists($entity, 'getId') && null !== $entity->getId()) {
                return 'delete' . $entity->getId();
            }

            return 'delete_' . \spl_object_hash($entity);
        });
    }
}
