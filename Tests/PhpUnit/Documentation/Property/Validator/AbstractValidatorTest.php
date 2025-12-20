<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\AbstractValidator;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Property\AbstractProperty;

class AbstractValidatorTest extends TestCase
{
    public function testAbstractValidator()
    {
        $property = $this->createMock(AbstractProperty::class);
        $validator = new class($property) extends AbstractValidator {
            public function validate($value): ValidatorResult
            {
                return new ValidatorResult(true);
            }
        };

        $this->assertSame($property, $validator->getProperty());
        $this->assertTrue($validator->validate('anything')->isSuccess());
    }
}
