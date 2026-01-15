<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\BooleanValidator;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Http\Request\Parameter\Parameter;

class BooleanValidatorTest extends TestCase
{
    public function testBooleanValidator()
    {
        $property = new BooleanProperty('test', '', true);
        $validator = new BooleanValidator($property);

        $this->assertTrue($validator->validate(new Parameter('test', true, true))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', false, false))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 1, 1))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 0, 0))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 'true', 'true'))->isSuccess());
        $this->assertTrue($validator->validate(new Parameter('test', 'false', 'false'))->isSuccess());
        
        $this->assertFalse($validator->validate(new Parameter('test', 'yes', 'yes'))->isSuccess());
        $this->assertFalse($validator->validate(new Parameter('test', 2, 2))->isSuccess());
    }
}
