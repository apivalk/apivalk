<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response;

use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Response\ValidationErrorObject;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;

class ValidationErrorObjectTest extends TestCase
{
    public function testErrorApivalkObject(): void
    {
        $object = new ValidationErrorObject();
        $this->assertEquals('error', $object->getPropertyName());
        $this->assertEquals('Error', $object->getMessage());
        $this->assertEquals('error', $object->getErrorKey());

        $object->populate('email', new ValidatorResult(false, ValidatorResult::FIELD_IS_REQUIRED));
        $this->assertEquals('This field is required.', $object->getMessage());
        $this->assertEquals('email', $object->getParameter());
        $this->assertEquals(ValidatorResult::FIELD_IS_REQUIRED, $object->getErrorKey());

        $this->assertInstanceOf(AbstractPropertyCollection::class, $object->getPropertyCollection());
    }
}
