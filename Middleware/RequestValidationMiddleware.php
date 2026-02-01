<?php

declare(strict_types=1);

namespace apivalk\apivalk\Middleware;

use apivalk\apivalk\Documentation\Property\AbstractProperty;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;
use apivalk\apivalk\Documentation\Response\ValidationErrorObject;
use apivalk\apivalk\Http\Controller\AbstractApivalkController;
use apivalk\apivalk\Http\Request\Parameter\ParameterBag;
use apivalk\apivalk\Http\Request\ApivalkRequestInterface;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;
use apivalk\apivalk\Http\Response\BadValidationApivalkResponse;

class RequestValidationMiddleware implements MiddlewareInterface
{
    /** @var ValidationErrorObject[] */
    private $errors = [];

    public function process(
        ApivalkRequestInterface $request,
        AbstractApivalkController $controller,
        callable $next
    ): AbstractApivalkResponse {
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
        /** @var AbstractProperty $property */
        foreach ($properties as $property) {
            $parameter = $parameterBag->get($property->getPropertyName());

            if ($parameter === null && !$property->isRequired()) {
                continue;
            }

            if ($parameter === null && $property->isRequired()) {
                $error = new ValidationErrorObject();
                $error->populate(
                    $property->getPropertyName(),
                    new ValidatorResult(false, ValidatorResult::FIELD_IS_REQUIRED)
                );

                $this->errors[] = $error;

                continue;
            }

            foreach ($property->getValidators() as $validator) {
                /** @var ValidatorResult $validatorResult */
                $validatorResult = $validator->validate($parameter);

                if (!$validatorResult->isSuccess()) {
                    $error = new ValidationErrorObject();
                    $error->populate($property->getPropertyName(), $validatorResult);

                    $this->errors[] = $error;
                }
            }
        }
    }
}
