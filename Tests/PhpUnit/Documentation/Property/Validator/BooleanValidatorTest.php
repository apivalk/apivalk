<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\BooleanValidator;
use apivalk\apivalk\Documentation\Property\BooleanProperty;

class BooleanValidatorTest extends TestCase
{
    public function testBooleanValidator()
    {
        $property = new BooleanProperty('test', '', true);
        $validator = new BooleanValidator($property);

        $this->assertTrue($validator->validate(true)->isSuccess());
        $this->assertTrue($validator->validate(false)->isSuccess());
        $this->assertTrue($validator->validate(1)->isSuccess());
        $this->assertTrue($validator->validate(0)->isSuccess());
        $this->assertTrue($validator->validate('true')->isSuccess());
        $this->assertTrue($validator->validate('false')->isSuccess());
        
        $this->assertFalse($validator->validate('yes')->isSuccess());
        $this->assertFalse($validator->validate(2)->isSuccess());
    }
}
