<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorFactory;
use apivalk\apivalk\Documentation\Property\Validator\StringValidator;
use apivalk\apivalk\Documentation\Property\Validator\EnumValidator;
use apivalk\apivalk\Documentation\Property\Validator\DateValidator;
use apivalk\apivalk\Documentation\Property\Validator\DateTimeValidator;
use apivalk\apivalk\Documentation\Property\Validator\ByteValidator;
use apivalk\apivalk\Documentation\Property\Validator\BinaryValidator;
use apivalk\apivalk\Documentation\Property\Validator\IntegerValidator;
use apivalk\apivalk\Documentation\Property\Validator\FloatValidator;
use apivalk\apivalk\Documentation\Property\Validator\BooleanValidator;
use apivalk\apivalk\Documentation\Property\Validator\ArrayValidator;
use apivalk\apivalk\Documentation\Property\Validator\ObjectValidator;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;

class ValidatorFactoryTest extends TestCase
{
    public function testValidatorFactory()
    {
        $this->assertInstanceOf(StringValidator::class, ValidatorFactory::create(new StringProperty('test')));
        $this->assertInstanceOf(EnumValidator::class, ValidatorFactory::create(new EnumProperty('test', '', ['a', 'b'])));
        $this->assertInstanceOf(DateValidator::class, ValidatorFactory::create(new DateProperty('test')));
        $this->assertInstanceOf(DateTimeValidator::class, ValidatorFactory::create(new DateTimeProperty('test')));
        $this->assertInstanceOf(ByteValidator::class, ValidatorFactory::create(new ByteProperty('test')));
        $this->assertInstanceOf(BinaryValidator::class, ValidatorFactory::create(new BinaryProperty('test')));

        $this->assertInstanceOf(IntegerValidator::class, ValidatorFactory::create(new IntegerProperty('test')));
        $this->assertInstanceOf(FloatValidator::class, ValidatorFactory::create(new FloatProperty('test')));

        $booleanProp = new BooleanProperty('test', '', true);
        $this->assertInstanceOf(BooleanValidator::class, ValidatorFactory::create($booleanProp));

        $objProp = $this->createMock(AbstractObjectProperty::class);
        $this->assertInstanceOf(ObjectValidator::class, ValidatorFactory::create($objProp));

        $arrayProp = new ArrayProperty('test', '', $objProp);
        $this->assertInstanceOf(ArrayValidator::class, ValidatorFactory::create($arrayProp));
    }
}
