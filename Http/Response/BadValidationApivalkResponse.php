<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Property\ArrayProperty;
use apivalk\apivalk\Documentation\Response\ErrorApivalkObject;

class BadValidationApivalkResponse extends AbstractApivalkResponse
{
    /** @var ErrorObject[] */
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
                new ErrorApivalkObject()
            )
        );

        return $responseDocumentation;
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_422_UNPROCESSABLE_ENTITY;
    }

    /**
     * @return ErrorObject[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        $errorArray = [];

        foreach ($this->errors as $error) {
            $errorArray[$error->getKey()] = $error->getMessage();
        }

        return ['errors' => $errorArray];
    }
}
