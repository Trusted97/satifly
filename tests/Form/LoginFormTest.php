<?php

namespace App\Tests\Form;

use App\Form\LoginType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

final class LoginFormTest extends TestCase
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
        $form = $this->factory->create(LoginType::class);

        $this->assertTrue($form->has('username'));
        $this->assertTrue($form->has('password'));
        $this->assertTrue($form->has('login'));
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'username' => 'testuser',
            'password' => 'secret',
            'login'    => true,
        ];

        $form = $this->factory->create(LoginType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('testuser', $form->get('username')->getData());
        $this->assertSame('secret', $form->get('password')->getData());
    }

    public function testEmptyData(): void
    {
        $form = $this->factory->create(LoginType::class);
        $form->submit([]);

        $this->assertTrue($form->isSynchronized());
        $this->assertNull($form->get('username')->getData());
        $this->assertNull($form->get('password')->getData());
    }
}
