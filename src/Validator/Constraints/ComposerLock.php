<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ComposerLock extends Constraint
{
    /**
     * Returns the path to the composer.lock Schema file.
     */
    public function getSchemaPath(): string
    {
        return __DIR__ . '/../../Resources/schemas/composer_lock.json';
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function getDefaultOption(): string
    {
        return '';
    }
}
