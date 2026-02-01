<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Response\ValidationErrorObject;

class BadValidationApivalkResponse extends AbstractApivalkResponse
{
    /** @var ValidationErrorObject[] */
    private $errors;

    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
    }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        $responseDocumentation = new ApivalkResponseDocumentation();

        $responseDocumentation->setDescription('Request validation failed');
        $responseDocumentation->addProperty(
            new ArrayProperty(
                'errors',
                'List of errors',
                new ValidationErrorObject()
            )
        );

        return $responseDocumentation;
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_422_UNPROCESSABLE_ENTITY;
    }

    /**
     * @return ValidationErrorObject[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        $errorArray = [];

        foreach ($this->errors as $error) {
            $errorArray[] = $error->toArray();
        }

        return ['errors' => $errorArray];
    }
}
