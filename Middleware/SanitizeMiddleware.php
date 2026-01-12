<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Request\Parameter\Parameter;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class SanitizeMiddleware implements MiddlewareInterface
{
    public function process(
        ApivalkRequestInterface $request,
        AbstractApivalkController $controller,
        callable $next
    ): AbstractApivalkResponse {
        $inputParameterBag = $request->body();
        $queryParameterBag = $request->query();
        $pathParameterBag = $request->path();

        foreach ($inputParameterBag->getIterator() as $inputParameter) {
            $this->sanitize($inputParameter);
            $inputParameterBag->set($inputParameter);
        }

        foreach ($queryParameterBag->getIterator() as $queryParameter) {
            $this->sanitize($queryParameter);
            $queryParameterBag->set($queryParameter);
        }

        foreach ($pathParameterBag->getIterator() as $pathParameter) {
            $this->sanitize($pathParameter);
            $pathParameterBag->set($pathParameter);
        }

        return $next($request);
    }

    private function sanitize(Parameter $parameter): void
    {
        if (!\is_string($parameter->getValue())) {
            return;
        }

        $parameter->setValue(
            htmlspecialchars(
                $parameter->getValue(),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            )
        );
    }
}
