<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Method;

final class MethodFactory
{
    public static function create(string $methodName): MethodInterface
    {
        switch (strtoupper($methodName)) {
            case MethodInterface::METHOD_GET:
                return new GetMethod();
            case MethodInterface::METHOD_POST:
                return new PostMethod();
            case MethodInterface::METHOD_DELETE:
                return new DeleteMethod();
            case MethodInterface::METHOD_PATCH:
                return new PatchMethod();
            case MethodInterface::METHOD_PUT:
                return new PutMethod();
        }

        throw new \InvalidArgumentException(\sprintf('Method "%s" is not supported', $methodName));
    }
}
