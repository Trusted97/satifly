<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Repository extends Constraint
{
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getDefaultOption(): string
    {
        return '';
    }
}
