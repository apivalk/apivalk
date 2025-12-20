<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ObjectValidator;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;

class ObjectValidatorTest extends TestCase
{
    public function testObjectValidator()
    {
        $objProp = $this->createMock(AbstractObjectProperty::class);
        $validator = new ObjectValidator($objProp);

        $this->assertTrue($validator->validate('{"a": 1}')->isSuccess());
        $this->assertFalse($validator->validate('not a json object')->isSuccess());
        $this->assertFalse($validator->validate(['already decoded'])->isSuccess());
    }
}
