<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Resource\Stub;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\FloatProperty;
use apivalk\apivalk\Documentation\Property\StringProperty;
use apivalk\apivalk\Resource\AbstractResource;

class AnimalResource extends AbstractResource
{
    public function getIdentifierProperty(): AbstractProperty
    {
        return new StringProperty('animal_uuid', 'Unique identifier of the animal');
    }

    public function getBaseUrl(): string
    {
        return '/api/v1';
    }

    public function getName(): string
    {
        return 'animal';
    }

    public function excludeFromMode(string $mode): array
    {
        if ($mode === self::MODE_LIST) {
            return ['weight'];
        }

        return [];
    }

    protected function init(): void
    {
        $this->addProperty(new StringProperty('name', 'Name of the animal'));
        $this->addProperty(new StringProperty('type', 'Type of the animal'));
        $this->addProperty((new FloatProperty('weight', 'Weight of the animal in kg'))->setIsRequired(false));
    }
}
