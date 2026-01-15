<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ObjectValidator;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class ObjectValidatorTest extends TestCase
{
    public function testObjectValidator()
    {
        $objProp = $this->createMock(AbstractObjectProperty::class);
        $validator = new ObjectValidator($objProp);

        $this->assertTrue($validator->validate(new Parameter('test', '{"a": 1}', '{"a": 1}'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 'not a json object', 'not a json object'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', ['already decoded'], ['already decoded']))->isSuccess());
    }
}
