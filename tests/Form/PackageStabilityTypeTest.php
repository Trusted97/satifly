<?php

namespace App\Tests\Form;

use App\DTO\PackageStability;
use App\Form\PackageStabilityType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

final class PackageStabilityTypeTest extends TestCase
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
        $form = $this->factory->create(PackageStabilityType::class);

        $this->assertTrue($form->has('package'));
        $this->assertTrue($form->has('stability'));
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'package'   => 'symfony/form',
            'stability' => 'stable',
        ];

        $form = $this->factory->create(PackageStabilityType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        /** @var PackageStability $data */
        $data = $form->getData();
        $this->assertSame('symfony/form', $data->getPackage());
        $this->assertSame('stable', $data->getStability());
    }

    public function testSubmitEmptyDataIsInvalid(): void
    {
        $form = $this->factory->create(PackageStabilityType::class);
        $form->submit(['package' => '', 'stability' => '']);

        $this->assertFalse($form->isValid());
        $errors = $form->getErrors(true);
        $this->assertCount(2, $errors);
    }

    public function testEmptyDataCreatesDefaultObject(): void
    {
        $form = $this->factory->create(PackageStabilityType::class);
        $form->submit([]);

        $this->assertTrue($form->isSynchronized());
        $data = $form->getData();
        $this->assertInstanceOf(PackageStability::class, $data);
        $this->assertSame('', $data->getPackage());
        $this->assertSame('', $data->getStability());
    }
}
