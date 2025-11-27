<?php

namespace App\Tests\Form;

use App\DTO\Archive;
use App\Form\ArchiveType;
use Symfony\Component\Form\Test\TypeTestCase;

final class ArchiveTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'directory'         => 'dist',
            'format'            => 'zip',
            'absoluteDirectory' => '/var/www/output',
            'prefixUrl'         => 'https://example.com/dist/',
            'skipDev'           => false,
            'checksum'          => false,
            'ignoreFilters'     => true,
            'overrideDistType'  => true,
            'rearchive'         => false,
            'whitelist'         => ['package1', 'package2'],
            'blacklist'         => ['bad1', 'bad2'],
        ];

        $model = new Archive();
        $form  = $this->factory->create(ArchiveType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $data = $form->getData();

        $this->assertSame('dist', $data->getDirectory());
        $this->assertSame('zip', $data->getFormat());
        $this->assertSame('/var/www/output', $data->getAbsoluteDirectory());
        $this->assertSame('https://example.com/dist/', $data->getPrefixUrl());
        $this->assertFalse($data->isSkipDev());
        $this->assertFalse($data->isChecksum());
        $this->assertTrue($data->isIgnoreFilters());
        $this->assertTrue($data->isOverrideDistType());
        $this->assertFalse($data->isRearchive());
        $this->assertSame(['package1', 'package2'], $data->getWhitelist());
        $this->assertSame(['bad1', 'bad2'], $data->getBlacklist());
    }

    public function testEmptyData(): void
    {
        $form = $this->factory->create(ArchiveType::class);
        $form->submit([]);

        $this->assertTrue($form->isSynchronized());
        $data = $form->getData();

        $this->assertSame('', $data->getDirectory());
        $this->assertSame('', $data->getFormat());
        $this->assertNull($data->getAbsoluteDirectory());
        $this->assertNull($data->getPrefixUrl());
        $this->assertFalse($data->isSkipDev());
        $this->assertFalse($data->isChecksum());
        $this->assertFalse($data->isIgnoreFilters());
        $this->assertFalse($data->isOverrideDistType());
        $this->assertFalse($data->isRearchive());
        $this->assertSame([], $data->getWhitelist());
        $this->assertSame([], $data->getBlacklist());
    }
}
