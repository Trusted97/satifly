<?php

namespace App\Tests\Traits;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

trait SchemaValidatorTrait
{
    protected function getSatisSchema(): \stdClass
    {
        return (new UriRetriever())->retrieve('file://' . __DIR__ . '/../../vendor/composer/satis/res/satis-schema.json');
    }

    protected function validateSchema($content, \stdClass $schema): void
    {
        $validator = new Validator();
        $validator->validate($content, $schema);
        $this->assertTrue($validator->isValid(), \print_r($validator->getErrors(), true));
    }
}
