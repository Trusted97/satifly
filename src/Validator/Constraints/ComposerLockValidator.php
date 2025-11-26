<?php

namespace App\Validator\Constraints;

use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Composer.lock validator
 *
 * @author Julius Beckmann <php@h4cc.de>
 */
class ComposerLockValidator extends ConstraintValidator
{
    /**
     * @throws \JsonException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!\is_object($value) || !$value instanceof UploadedFile) {
            throw new InvalidArgumentException(\sprintf('This validator expects a UploadedFile, given "%s"', $value::class));
        }

        $composerData = \json_decode(\file_get_contents($value->openFile()->getRealPath()), false, 512, \JSON_THROW_ON_ERROR);
        $schema       = null;
        if ($constraint instanceof ComposerLock) {
            $schema = $this->getSchema($constraint->getSchemaPath());
        }

        // In version 1.1.0 of the validator, "required" attributes are not used.
        // So data structure might be partially unset.
        $validator = new Validator();
        $validator->validate($composerData, $schema);

        if (!$validator->isValid()) {
            $this->context->addViolation('Invalid composer.lock file given:');
        }

        foreach ($validator->getErrors() as $error) {
            $this->context->addViolation(\sprintf("[%s] %s\n", $error['property'], $error['message']));
        }
    }

    /**
     * Returns schema data for validation.
     *
     * @throws \JsonException
     */
    private function getSchema(string $path)
    {
        $schema_json = \file_get_contents($path);

        return \json_decode($schema_json, false, 512, \JSON_THROW_ON_ERROR);
    }
}
