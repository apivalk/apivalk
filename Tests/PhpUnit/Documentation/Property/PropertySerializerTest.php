<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Property;

use PHPUnit\Framework\TestCase;
use apivalk\apivalk\Documentation\Property\BinaryProperty;
use apivalk\apivalk\Documentation\Property\BooleanProperty;
use apivalk\apivalk\Documentation\Property\ByteProperty;
use apivalk\apivalk\Documentation\Property\DateProperty;
use apivalk\apivalk\Documentation\Property\DateTimeProperty;
use apivalk\apivalk\Documentation\Property\EnumProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\IntegerProperty;
use apivalk\apivalk\Documentation\Property\PropertySerializer;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Documentation\Property\Validator\StringValidator;
use apivalk\apivalk\Documentation\Property\Validator\EnumValidator;
use apivalk\apivalk\Documentation\Property\Validator\DateValidator;
use apivalk\apivalk\Documentation\Property\Validator\DateTimeValidator;
use apivalk\apivalk\Documentation\Property\Validator\ByteValidator;
use apivalk\apivalk\Documentation\Property\Validator\BinaryValidator;
use apivalk\apivalk\Documentation\Property\Validator\IntegerValidator;
use apivalk\apivalk\Documentation\Property\Validator\FloatValidator;
use apivalk\apivalk\Documentation\Property\Validator\BooleanValidator;

class PropertySerializerTest extends TestCase
{
    public function testStringProperty(): void
    {
        $property = new StringProperty('name', 'User name');
        $property->setDefault('John')
            ->setMinLength(2)
            ->setMaxLength(50)
            ->setPattern('^[a-zA-Z]+$')
            ->setIsRequired(true)
            ->setExample('Jane');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(StringProperty::class, $restored);
        $this->assertEquals('name', $restored->getPropertyName());
        $this->assertEquals('User name', $restored->getPropertyDescription());
        $this->assertEquals('John', $restored->getDefault());
        $this->assertEquals(2, $restored->getMinLength());
        $this->assertEquals(50, $restored->getMaxLength());
        $this->assertEquals('^[a-zA-Z]+$', $restored->getPattern());
        $this->assertTrue($restored->isRequired());
        $this->assertEquals('Jane', $restored->getExample());
    }

    public function testEnumProperty(): void
    {
        $property = new EnumProperty('status', 'Contract status', ['active', 'inactive', 'pending']);
        $property->setDefault('active')
            ->setIsRequired(false)
            ->setExample('inactive');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(EnumProperty::class, $restored);
        $this->assertEquals('status', $restored->getPropertyName());
        $this->assertEquals('Contract status', $restored->getPropertyDescription());
        $this->assertEquals(['active', 'inactive', 'pending'], $restored->getEnums());
        $this->assertEquals('active', $restored->getDefault());
        $this->assertFalse($restored->isRequired());
        $this->assertEquals('inactive', $restored->getExample());
    }

    public function testDateProperty(): void
    {
        $property = new DateProperty('birthdate', 'Date of birth');
        $property->setDefault('2000-01-01')
            ->setExample('1990-05-15');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(DateProperty::class, $restored);
        $this->assertEquals('birthdate', $restored->getPropertyName());
        $this->assertEquals('2000-01-01', $restored->getDefault());
        $this->assertEquals('date', $restored->getFormat());
        $this->assertEquals('1990-05-15', $restored->getExample());
    }

    public function testDateTimeProperty(): void
    {
        $property = new DateTimeProperty('createdAt', 'Creation timestamp');
        $property->setDefault('2024-01-01T00:00:00Z')
            ->setExample('2024-06-15T12:30:00+02:00');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(DateTimeProperty::class, $restored);
        $this->assertEquals('createdAt', $restored->getPropertyName());
        $this->assertEquals('2024-01-01T00:00:00Z', $restored->getDefault());
        $this->assertEquals('date-time', $restored->getFormat());
        $this->assertEquals('2024-06-15T12:30:00+02:00', $restored->getExample());
    }

    public function testByteProperty(): void
    {
        $property = new ByteProperty('payload', 'Base64 payload');
        $property->setDefault('dGVzdA==')
            ->setMinLength(4)
            ->setMaxLength(1024)
            ->setPattern('^[A-Za-z0-9+/=]+$');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(ByteProperty::class, $restored);
        $this->assertEquals('payload', $restored->getPropertyName());
        $this->assertEquals('dGVzdA==', $restored->getDefault());
        $this->assertEquals(4, $restored->getMinLength());
        $this->assertEquals(1024, $restored->getMaxLength());
        $this->assertEquals('^[A-Za-z0-9+/=]+$', $restored->getPattern());
        $this->assertEquals('byte', $restored->getFormat());
    }

    public function testBinaryProperty(): void
    {
        $property = new BinaryProperty('file', 'Binary file');
        $property->setDefault('binary_data')
            ->setMinLength(1)
            ->setMaxLength(2048)
            ->setPattern('^.+$');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(BinaryProperty::class, $restored);
        $this->assertEquals('file', $restored->getPropertyName());
        $this->assertEquals('binary_data', $restored->getDefault());
        $this->assertEquals(1, $restored->getMinLength());
        $this->assertEquals(2048, $restored->getMaxLength());
        $this->assertEquals('^.+$', $restored->getPattern());
        $this->assertEquals('binary', $restored->getFormat());
    }

    public function testIntegerPropertyInt32(): void
    {
        $property = new IntegerProperty('age', 'User age', IntegerProperty::FORMAT_INT32);
        $property->setMinimumValue(0)
            ->setMaximumValue(150)
            ->setIsExclusiveMinimum(true)
            ->setIsExclusiveMaximum(false)
            ->setExample('25');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(IntegerProperty::class, $restored);
        $this->assertEquals('age', $restored->getPropertyName());
        $this->assertEquals('int32', $restored->getFormat());
        $this->assertEquals(0, $restored->getMinimumValue());
        $this->assertEquals(150, $restored->getMaximumValue());
        $this->assertTrue($restored->isExclusiveMinimum());
        $this->assertFalse($restored->isExclusiveMaximum());
        $this->assertEquals('25', $restored->getExample());
    }

    public function testIntegerPropertyInt64(): void
    {
        $property = new IntegerProperty('id', 'Record ID');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(IntegerProperty::class, $restored);
        $this->assertEquals('int64', $restored->getFormat());
        $this->assertNull($restored->getMinimumValue());
        $this->assertNull($restored->getMaximumValue());
        $this->assertNull($restored->isExclusiveMinimum());
        $this->assertNull($restored->isExclusiveMaximum());
    }

    public function testFloatPropertyFloat(): void
    {
        $property = new FloatProperty('price', 'Product price', FloatProperty::FORMAT_FLOAT);
        $property->setMinimumValue(0.01)
            ->setMaximumValue(9999.99)
            ->setIsExclusiveMinimum(false)
            ->setIsExclusiveMaximum(true)
            ->setExample('19.99');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(FloatProperty::class, $restored);
        $this->assertEquals('price', $restored->getPropertyName());
        $this->assertEquals('float', $restored->getFormat());
        $this->assertEquals(0.01, $restored->getMinimumValue());
        $this->assertEquals(9999.99, $restored->getMaximumValue());
        $this->assertFalse($restored->isExclusiveMinimum());
        $this->assertTrue($restored->isExclusiveMaximum());
        $this->assertEquals('19.99', $restored->getExample());
    }

    public function testFloatPropertyDouble(): void
    {
        $property = new FloatProperty('ratio', 'Ratio value');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(FloatProperty::class, $restored);
        $this->assertEquals('double', $restored->getFormat());
    }

    public function testBooleanProperty(): void
    {
        $property = new BooleanProperty('active', 'Is active', true);
        $property->setExample('true');

        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertInstanceOf(BooleanProperty::class, $restored);
        $this->assertEquals('active', $restored->getPropertyName());
        $this->assertTrue($restored->getDefault());
        $this->assertEquals('true', $restored->getExample());
    }

    public function testJsonRoundTrip(): void
    {
        $property = new EnumProperty('status', 'Status', ['active', 'inactive']);
        $property->setDefault('active');

        $data = PropertySerializer::serialize($property);
        $json = json_encode($data);
        $decoded = json_decode($json, true);
        $restored = PropertySerializer::deserialize($decoded);

        $this->assertInstanceOf(EnumProperty::class, $restored);
        $this->assertEquals(['active', 'inactive'], $restored->getEnums());
        $this->assertEquals('active', $restored->getDefault());
    }

    public function testUnknownClassThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PropertySerializer::deserialize([
            'class' => 'NonExistent\\Property',
            'name' => 'test',
            'description' => '',
        ]);
    }

    public function testValidatorsAreSerialized(): void
    {
        $property = new StringProperty('test', 'Test');
        $property->init();

        $data = PropertySerializer::serialize($property);

        $this->assertNotEmpty($data['validators']);
        $this->assertContains(StringValidator::class, $data['validators']);
    }

    public function testDeserializeRestoresValidatorsForString(): void
    {
        $property = new StringProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(StringValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForEnum(): void
    {
        $property = new EnumProperty('test', 'Test', ['a', 'b']);
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(EnumValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForDate(): void
    {
        $property = new DateProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(DateValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForDateTime(): void
    {
        $property = new DateTimeProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(DateTimeValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForByte(): void
    {
        $property = new ByteProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(ByteValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForBinary(): void
    {
        $property = new BinaryProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(BinaryValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForInteger(): void
    {
        $property = new IntegerProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(IntegerValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForFloat(): void
    {
        $property = new FloatProperty('test', 'Test');
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(FloatValidator::class, \array_values($restored->getValidators())[0]);
    }

    public function testDeserializeRestoresValidatorsForBoolean(): void
    {
        $property = new BooleanProperty('test', 'Test', false);
        $property->init();
        $data = PropertySerializer::serialize($property);
        $restored = PropertySerializer::deserialize($data);

        $this->assertNotEmpty($restored->getValidators());
        $this->assertInstanceOf(BooleanValidator::class, \array_values($restored->getValidators())[0]);
    }
}
