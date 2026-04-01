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
        $this->assertEquals('Error', $object->getPropertyDescription());
        $this->assertEquals('error', $object->getErrorKey());

        $error = ValidationErrorObject::create('email', 'This field is required.', ValidatorResult::FIELD_IS_REQUIRED);
        $this->assertEquals('This field is required.', $error->getMessage());
        $this->assertEquals('email', $error->getParameter());
        $this->assertEquals(ValidatorResult::FIELD_IS_REQUIRED, $error->getErrorKey());

        $this->assertInstanceOf(AbstractPropertyCollection::class, $error->getPropertyCollection());
    }

    public function testWithoutValidatorResult(): void
    {
        $validationErrorObject = ValidationErrorObject::create('test', 'testMsg', 'testKey');

        $this->assertEquals('error', $validationErrorObject->getPropertyName());
        $this->assertEquals('Error', $validationErrorObject->getPropertyDescription());

        $this->assertEquals('test', $validationErrorObject->getParameter());
        $this->assertEquals('testMsg', $validationErrorObject->getMessage());
        $this->assertEquals('testKey', $validationErrorObject->getErrorKey());

        $this->assertinstanceOf(AbstractPropertyCollection::class, $validationErrorObject->getPropertyCollection());
    }
}
