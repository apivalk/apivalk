<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Resource\AbstractResource;

final class ResponseDocumentationFactory
{
    /**
     * Response documentation for a resource in a given mode, used by the OpenAPI generator.
     */
    public static function create(
        AbstractResource $resource,
        string $mode
    ): ApivalkResponseDocumentation {
        $documentation = new ApivalkResponseDocumentation();
        $documentation->setDescription(\ucfirst($mode) . ' ' . $resource->getName() . ' response');

        $excluded = $resource->excludeFromMode($mode);

        $documentation->addProperty($resource->getIdentifierProperty());

        foreach ($resource->getProperties() as $property) {
            if (\in_array($property->getPropertyName(), $excluded, true)) {
                continue;
            }

            $documentation->addProperty($property);
        }

        return $documentation;
    }
}
