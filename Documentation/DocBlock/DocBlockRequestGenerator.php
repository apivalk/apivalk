<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\DocBlock;

use apivalk\apivalk\Http\Request\AbstractApivalkRequest;
use apivalk\apivalk\Router\Route\Order\Order;
use apivalk\apivalk\Router\Route\Route;

final class DocBlockRequestGenerator
{
    public function generate(AbstractApivalkRequest $abstractApivalkRequest, Route $route): DocBlockRequest
    {
        $documentation = $abstractApivalkRequest::getDocumentation();

        $requestName = (new \ReflectionClass($abstractApivalkRequest))->getShortName();

        $bodyShape = new DocBlockShape($requestName, 'Body');
        $pathShape = new DocBlockShape($requestName, 'Path');
        $queryShape = new DocBlockShape($requestName, 'Query');
        $orderingShape = new DocBlockShape($requestName, 'Ordering');

        foreach ($documentation->getBodyProperties() as $property) {
            $bodyShape->addProperty($property);
        }

        foreach ($documentation->getPathProperties() as $property) {
            $pathShape->addProperty($property);
        }

        foreach ($documentation->getQueryProperties() as $property) {
            $queryShape->addProperty($property);
        }

        foreach ($route->getOrderings() as $ordering) {
            $orderingShape->addCustomField($ordering->getField(), '\\' . Order::class . '|null');
        }

        return new DocBlockRequest(
            $bodyShape,
            $pathShape,
            $queryShape,
            $orderingShape
        );
    }
}
