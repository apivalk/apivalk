<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Property;

class PropertySerializer
{
    public static function serialize(AbstractProperty $property): array
    {
        if ($property instanceof StringProperty) {
            return self::serializeStringProperty($property);
        }

        if ($property instanceof EnumProperty) {
            return self::serializeEnumProperty($property);
        }

        if ($property instanceof DateProperty) {
            return self::serializeDateProperty($property);
        }

        if ($property instanceof DateTimeProperty) {
            return self::serializeDateTimeProperty($property);
        }

        if ($property instanceof ByteProperty) {
            return self::serializeByteProperty($property);
        }

        if ($property instanceof BinaryProperty) {
            return self::serializeBinaryProperty($property);
        }

        if ($property instanceof IntegerProperty) {
            return self::serializeIntegerProperty($property);
        }

        if ($property instanceof FloatProperty) {
            return self::serializeFloatProperty($property);
        }

        if ($property instanceof BooleanProperty) {
            return self::serializeBooleanProperty($property);
        }

        return self::serializeBase($property);
    }

    public static function deserialize(array $data): AbstractProperty
    {
        $class = $data['class'];

        switch ($class) {
            case StringProperty::class:
                $property = self::deserializeStringProperty($data);
                break;
            case EnumProperty::class:
                $property = self::deserializeEnumProperty($data);
                break;
            case DateProperty::class:
                $property = self::deserializeDateProperty($data);
                break;
            case DateTimeProperty::class:
                $property = self::deserializeDateTimeProperty($data);
                break;
            case ByteProperty::class:
                $property = self::deserializeByteProperty($data);
                break;
            case BinaryProperty::class:
                $property = self::deserializeBinaryProperty($data);
                break;
            case IntegerProperty::class:
                $property = self::deserializeIntegerProperty($data);
                break;
            case FloatProperty::class:
                $property = self::deserializeFloatProperty($data);
                break;
            case BooleanProperty::class:
                $property = self::deserializeBooleanProperty($data);
                break;
            default:
                throw new \InvalidArgumentException(\sprintf('Unknown property class "%s"', $class));
        }

        if (isset($data['isRequired'])) {
            $property->setIsRequired($data['isRequired']);
        }

        if (isset($data['example'])) {
            $property->setExample($data['example']);
        }

        if (!empty($data['validators'])) {
            foreach (\array_values($data['validators']) as $validatorClass) {
                $property->addValidator(new $validatorClass($property));
            }
        } else {
            $property->init();
        }

        return $property;
    }

    private static function serializeBase(AbstractProperty $property): array
    {
        $validators = [];

        foreach ($property->getValidators() as $validator) {
            $validators[] = \get_class($validator);
        }

        return [
            'class' => \get_class($property),
            'name' => $property->getPropertyName(),
            'description' => $property->getPropertyDescription(),
            'isRequired' => $property->isRequired(),
            'example' => $property->getExample(),
            'validators' => $validators,
        ];
    }

    private static function serializeStringProperty(StringProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['default'] = $property->getDefault();
        $data['minLength'] = $property->getMinLength();
        $data['maxLength'] = $property->getMaxLength();
        $data['pattern'] = $property->getPattern();

        return $data;
    }

    private static function deserializeStringProperty(array $data): StringProperty
    {
        $property = new StringProperty($data['name'], $data['description']);

        if (isset($data['default'])) {
            $property->setDefault($data['default']);
        }
        if (isset($data['minLength'])) {
            $property->setMinLength($data['minLength']);
        }
        if (isset($data['maxLength'])) {
            $property->setMaxLength($data['maxLength']);
        }
        if (isset($data['pattern'])) {
            $property->setPattern($data['pattern']);
        }

        return $property;
    }

    private static function serializeEnumProperty(EnumProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['enums'] = $property->getEnums();
        $data['default'] = $property->getDefault();

        return $data;
    }

    private static function deserializeEnumProperty(array $data): EnumProperty
    {
        $property = new EnumProperty($data['name'], $data['description'], $data['enums'] ?? []);

        if (isset($data['default'])) {
            $property->setDefault($data['default']);
        }

        return $property;
    }

    private static function serializeDateProperty(DateProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['default'] = $property->getDefault();

        return $data;
    }

    private static function deserializeDateProperty(array $data): DateProperty
    {
        $property = new DateProperty($data['name'], $data['description']);

        if (isset($data['default'])) {
            $property->setDefault($data['default']);
        }

        return $property;
    }

    private static function serializeDateTimeProperty(DateTimeProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['default'] = $property->getDefault();

        return $data;
    }

    private static function deserializeDateTimeProperty(array $data): DateTimeProperty
    {
        $property = new DateTimeProperty($data['name'], $data['description']);

        if (isset($data['default'])) {
            $property->setDefault($data['default']);
        }

        return $property;
    }

    private static function serializeByteProperty(ByteProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['default'] = $property->getDefault();
        $data['minLength'] = $property->getMinLength();
        $data['maxLength'] = $property->getMaxLength();
        $data['pattern'] = $property->getPattern();

        return $data;
    }

    private static function deserializeByteProperty(array $data): ByteProperty
    {
        $property = new ByteProperty($data['name'], $data['description']);

        if (isset($data['default'])) {
            $property->setDefault($data['default']);
        }
        if (isset($data['minLength'])) {
            $property->setMinLength($data['minLength']);
        }
        if (isset($data['maxLength'])) {
            $property->setMaxLength($data['maxLength']);
        }
        if (isset($data['pattern'])) {
            $property->setPattern($data['pattern']);
        }

        return $property;
    }

    private static function serializeBinaryProperty(BinaryProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['default'] = $property->getDefault();
        $data['minLength'] = $property->getMinLength();
        $data['maxLength'] = $property->getMaxLength();
        $data['pattern'] = $property->getPattern();

        return $data;
    }

    private static function deserializeBinaryProperty(array $data): BinaryProperty
    {
        $property = new BinaryProperty($data['name'], $data['description']);

        if (isset($data['default'])) {
            $property->setDefault($data['default']);
        }
        if (isset($data['minLength'])) {
            $property->setMinLength($data['minLength']);
        }
        if (isset($data['maxLength'])) {
            $property->setMaxLength($data['maxLength']);
        }
        if (isset($data['pattern'])) {
            $property->setPattern($data['pattern']);
        }

        return $property;
    }

    private static function serializeIntegerProperty(IntegerProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['format'] = $property->getFormat();
        $data['minimumValue'] = $property->getMinimumValue();
        $data['maximumValue'] = $property->getMaximumValue();
        $data['exclusiveMinimum'] = $property->isExclusiveMinimum();
        $data['exclusiveMaximum'] = $property->isExclusiveMaximum();

        return $data;
    }

    private static function deserializeIntegerProperty(array $data): IntegerProperty
    {
        $property = new IntegerProperty(
            $data['name'],
            $data['description'],
            $data['format'] ?? IntegerProperty::FORMAT_INT64
        );

        if (isset($data['minimumValue'])) {
            $property->setMinimumValue($data['minimumValue']);
        }
        if (isset($data['maximumValue'])) {
            $property->setMaximumValue($data['maximumValue']);
        }
        if (isset($data['exclusiveMinimum'])) {
            $property->setIsExclusiveMinimum($data['exclusiveMinimum']);
        }
        if (isset($data['exclusiveMaximum'])) {
            $property->setIsExclusiveMaximum($data['exclusiveMaximum']);
        }

        return $property;
    }

    private static function serializeFloatProperty(FloatProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['format'] = $property->getFormat();
        $data['minimumValue'] = $property->getMinimumValue();
        $data['maximumValue'] = $property->getMaximumValue();
        $data['exclusiveMinimum'] = $property->isExclusiveMinimum();
        $data['exclusiveMaximum'] = $property->isExclusiveMaximum();

        return $data;
    }

    private static function deserializeFloatProperty(array $data): FloatProperty
    {
        $property = new FloatProperty(
            $data['name'],
            $data['description'],
            $data['format'] ?? FloatProperty::FORMAT_DOUBLE
        );

        if (isset($data['minimumValue'])) {
            $property->setMinimumValue($data['minimumValue']);
        }
        if (isset($data['maximumValue'])) {
            $property->setMaximumValue($data['maximumValue']);
        }
        if (isset($data['exclusiveMinimum'])) {
            $property->setIsExclusiveMinimum($data['exclusiveMinimum']);
        }
        if (isset($data['exclusiveMaximum'])) {
            $property->setIsExclusiveMaximum($data['exclusiveMaximum']);
        }

        return $property;
    }

    private static function serializeBooleanProperty(BooleanProperty $property): array
    {
        $data = self::serializeBase($property);

        $data['default'] = $property->getDefault();

        return $data;
    }

    private static function deserializeBooleanProperty(array $data): BooleanProperty
    {
        return new BooleanProperty(
            $data['name'],
            $data['description'],
            $data['default'] ?? false
        );
    }
}
