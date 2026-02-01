<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;

class ValidatorResultTest extends TestCase
{
    public function testValidatorResult()
    {
        $result = new ValidatorResult(true);
        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->getErrorKey());

        $result = new ValidatorResult(false, ValidatorResult::VALUE_IS_NOT_NUMERIC);
        $this->assertFalse($result->isSuccess());
        $this->assertEquals(ValidatorResult::VALUE_IS_NOT_NUMERIC, $result->getErrorKey());
        $this->assertEquals('This value is not numeric.', $result->getLocalizedErrorMessage());
    }
}
