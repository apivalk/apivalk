<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;
use apivalk\apivalk\Http\Response\ErrorObject;

class RequestValidationMiddleware implements MiddlewareInterface
{
    /** @var ErrorObject[] */
    private $errors = [];

    public function process(ApivalkRequestInterface $request, string $controllerClass, callable $next): AbstractApivalkResponse
    {
        $this->errors = [];

        $documentation = $request::getDocumentation();

        $this->validateProperties(
            $documentation->getBodyProperties(),
            $request->body()
        );

        $this->validateProperties(
            $documentation->getQueryProperties(),
            $request->query()
        );

        $this->validateProperties(
            $documentation->getPathProperties(),
            $request->path()
        );

        if (\count($this->errors) > 0) {
            return new BadValidationApivalkResponse($this->errors);
        }

        return $next($request);
    }

    private function validateProperties(
        array $properties,
        ParameterBag $parameterBag
    ): void {
        foreach ($properties as $property) {
            $parameter = $parameterBag->get($property->getPropertyName());

            if ($parameter === null && !$property->isRequired()) {
                continue;
            }

            if ($parameter === null && $property->isRequired()) {
                $this->errors[] = new ErrorObject($property->getPropertyName(), ValidatorResult::FIELD_IS_REQUIRED);
                continue;
            }

            foreach ($property->getValidators() as $validator) {
                $validationResult = $validator->validate($parameter->getValue());

                if (!$validationResult->isSuccess()) {
                    $this->errors[] = new ErrorObject(
                        $property->getPropertyName(), $validationResult->getMessage()
                    );
                }
            }
        }
    }
}
