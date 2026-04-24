<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Resource\AbstractResource;

final class DocBlockResourceGenerator
{
    public function generate(AbstractResource $resource): DocBlockResource
    {
        $docBlockResource = new DocBlockResource();

        $docBlockResource->addProperty($resource->getIdentifierProperty());

        foreach ($resource->getProperties() as $property) {
            $docBlockResource->addProperty($property);
        }

        return $docBlockResource;
    }
}
