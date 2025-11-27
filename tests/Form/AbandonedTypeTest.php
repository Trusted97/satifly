<?php

namespace App\Tests\Form;

use App\DTO\Abandoned;
use App\Form\AbandonedType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class AbandonedTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [new ValidatorExtension($validator)];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'package'     => 'deprecated/package',
            'replacement' => 'new/package',
        ];

        $objectToCompare = new Abandoned('', null);
        $form            = $this->factory->create(AbandonedType::class, $objectToCompare);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $expected = new Abandoned('deprecated/package', 'new/package');
        $this->assertEquals($expected, $form->getData());

        $view = $form->createView();
        $this->assertArrayHasKey('package', $view->children);
        $this->assertArrayHasKey('replacement', $view->children);
    }

    public function testSubmitEmptyReplacementIsValid(): void
    {
        $formData = [
            'package'     => 'deprecated/package',
            'replacement' => '',
        ];

        $form = $this->factory->create(AbandonedType::class, new Abandoned('', null));
        $form->submit($formData);

        $this->assertTrue($form->isValid());
        $this->assertSame('deprecated/package', $form->getData()->getPackage());
        $this->assertSame('', $form->getData()->getReplacement());
    }

    public function testPackageCannotBeBlank(): void
    {
        $formData = [
            'package'     => '',
            'replacement' => 'any',
        ];

        $form = $this->factory->create(AbandonedType::class, new Abandoned('', null));
        $form->submit($formData);

        $this->assertFalse($form->isValid());
        $errors = $form->get('package')->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('This value should not be blank', $errors[0]->getMessage());
    }

    public function testFieldPlaceholders(): void
    {
        $form = $this->factory->create(AbandonedType::class);
        $view = $form->createView();

        $this->assertSame('Abandoned package name', $view->children['package']->vars['attr']['placeholder']);
        $this->assertSame('Package name/URL pointing to a recommended alternative(can be empty)', $view->children['replacement']->vars['attr']['placeholder']);
    }
}
