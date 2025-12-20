<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property\Validator;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorFactory;
use apivalk\apivalk\Documentation\Property\Validator\StringValidator;
use apivalk\apivalk\Documentation\Property\Validator\NumberValidator;
use apivalk\apivalk\Documentation\Property\Validator\BooleanValidator;
use apivalk\apivalk\Documentation\Property\Validator\ArrayValidator;
use apivalk\apivalk\Documentation\Property\Validator\ObjectValidator;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\NumberProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;

class ValidatorFactoryTest extends TestCase
{
    public function testValidatorFactory()
    {
        $stringProp = new StringProperty('test');
        $this->assertInstanceOf(StringValidator::class, ValidatorFactory::create($stringProp));

        $numberProp = new NumberProperty('test');
        $this->assertInstanceOf(NumberValidator::class, ValidatorFactory::create($numberProp));

        $booleanProp = new BooleanProperty('test', '', true);
        $this->assertInstanceOf(BooleanValidator::class, ValidatorFactory::create($booleanProp));

        $objProp = $this->createMock(AbstractObjectProperty::class);
        $this->assertInstanceOf(ObjectValidator::class, ValidatorFactory::create($objProp));

        $arrayProp = new ArrayProperty('test', '', $objProp);
        $this->assertInstanceOf(ArrayValidator::class, ValidatorFactory::create($arrayProp));
    }
}
