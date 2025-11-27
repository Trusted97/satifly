<?php

namespace App\Tests\Form;

use App\DTO\PackageConstraint;
use App\Form\PackageConstraintType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

final class PackageConstraintTypeTest extends TestCase
{
    private FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $validator     = Validation::createValidator();
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions([new ValidatorExtension($validator)])
            ->getFormFactory();
    }

    public function testFormHasFields(): void
    {
        $form = $this->factory->create(PackageConstraintType::class);

        $this->assertTrue($form->has('package'));
        $this->assertTrue($form->has('constraint'));
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'package'    => 'symfony/form',
            'constraint' => '^6.0',
        ];

        $form = $this->factory->create(PackageConstraintType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        /** @var PackageConstraint $data */
        $data = $form->getData();
        $this->assertSame('symfony/form', $data->getPackage());
        $this->assertSame('^6.0', $data->getConstraint());
    }

    public function testSubmitEmptyDataIsInvalid(): void
    {
        $form = $this->factory->create(PackageConstraintType::class);
        $form->submit(['package' => '', 'constraint' => '']);

        $this->assertFalse($form->isValid());
        $errors = $form->getErrors(true);
        $this->assertCount(2, $errors);
    }

    public function testEmptyDataCreatesDefaultObject(): void
    {
        $form = $this->factory->create(PackageConstraintType::class);
        $form->submit([]);

        $this->assertTrue($form->isSynchronized());
        $data = $form->getData();
        $this->assertInstanceOf(PackageConstraint::class, $data);
        $this->assertSame('', $data->getPackage());
        $this->assertSame('', $data->getConstraint());
    }
}
