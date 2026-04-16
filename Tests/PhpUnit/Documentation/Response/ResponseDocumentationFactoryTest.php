<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Documentation\Response;

use apivalk\apivalk\Documentation\Response\ResponseDocumentationFactory;
use apivalk\apivalk\Resource\AbstractResource;
use apivalk\apivalk\Tests\PhpUnit\Resource\Stub\AnimalResource;
use PHPUnit\Framework\TestCase;

class ResponseDocumentationFactoryTest extends TestCase
{
    public function testViewModeIncludesAllProperties(): void
    {
        $doc = ResponseDocumentationFactory::create(new AnimalResource(), AbstractResource::MODE_VIEW);

        $names = array_map(static function ($p) {
            return $p->getPropertyName();
        }, $doc->getProperties());

        self::assertContains('animal_uuid', $names);
        self::assertContains('name', $names);
        self::assertContains('type', $names);
        self::assertContains('weight', $names);
    }

    public function testListModeExcludesWeightForAnimalResource(): void
    {
        $doc = ResponseDocumentationFactory::create(new AnimalResource(), AbstractResource::MODE_LIST);

        $names = array_map(static function ($p) {
            return $p->getPropertyName();
        }, $doc->getProperties());

        self::assertContains('animal_uuid', $names);
        self::assertContains('name', $names);
        self::assertNotContains('weight', $names);
    }

    public function testDescriptionIsSet(): void
    {
        $doc = ResponseDocumentationFactory::create(new AnimalResource(), AbstractResource::MODE_VIEW);

        self::assertSame('View animal response', $doc->getDescription());
    }

    public function testIdentifierIsAlwaysIncluded(): void
    {
        foreach ([
            AbstractResource::MODE_CREATE,
            AbstractResource::MODE_UPDATE,
            AbstractResource::MODE_LIST,
            AbstractResource::MODE_VIEW,
            AbstractResource::MODE_DELETE,
        ] as $mode) {
            $doc = ResponseDocumentationFactory::create(new AnimalResource(), $mode);

            $names = array_map(static function ($p) {
                return $p->getPropertyName();
            }, $doc->getProperties());

            self::assertContains('animal_uuid', $names, "Identifier missing for mode: $mode");
        }
    }
}
