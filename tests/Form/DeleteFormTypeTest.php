<?php

namespace App\Tests\Form;

use App\Form\DeleteFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

final class DeleteFormTypeTest extends TestCase
{
    private FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactory();
    }

    public function testCsrfTokenIdWithEntityHavingId(): void
    {
        $entity = new class {
            private ?int $id = 42;

            public function getId(): ?int
            {
                return $this->id;
            }
        };

        $form = $this->factory->create(DeleteFormType::class, null, [
            'entity' => $entity,
        ]);

        $config = $form->getConfig();
        $this->assertTrue($config->getOption('csrf_protection'));
        $this->assertSame('delete42', $config->getOption('csrf_token_id'));
    }

    public function testCsrfTokenIdWithEntityWithoutId(): void
    {
        $entity = new class {};

        $form = $this->factory->create(DeleteFormType::class, null, [
            'entity' => $entity,
        ]);

        $csrfTokenId = $form->getConfig()->getOption('csrf_token_id');
        $this->assertStringStartsWith('delete_', $csrfTokenId);
    }

    public function testRequiresEntityOption(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->factory->create(DeleteFormType::class);
    }
}
