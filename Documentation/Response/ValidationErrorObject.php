<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;
use apivalk\apivalk\Documentation\Property\Validator\ValidatorResult;

class ValidationErrorObject extends AbstractObjectProperty
{
    /** @var string */
    private $errorKey = 'error';
    /** @var string */
    private $message = 'Error';
    /** @var string */
    private $parameter = 'error';

    final public function __construct()
    {
        parent::__construct('error', 'Error');
    }

    public static function create(string $parameter, string $message, string $errorKey): self
    {
        $errorObject = new self();

        $errorObject->parameter = $parameter;
        $errorObject->errorKey = $errorKey;
        $errorObject->message = $message;

        return $errorObject;
    }

    public static function createByValidatorResult(string $parameter, ValidatorResult $validatorResult): self
    {
        $errorObject = new self();

        $errorObject->parameter = $parameter;
        $errorObject->errorKey = $validatorResult->getErrorKey();
        $errorObject->message = $validatorResult->getLocalizedErrorMessage();

        return $errorObject;
    }

    public function getErrorKey(): string
    {
        return $this->errorKey;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function getPropertyCollection(): AbstractPropertyCollection
    {
        return new ValidationErrorObjectPropertyCollection(AbstractPropertyCollection::MODE_VIEW);
    }

    /** @return array{parameter: string, message: string, key: string} */
    public function toArray(): array
    {
        return [
            'parameter' => $this->parameter,
            'message' => $this->message,
            'key' => $this->errorKey,
        ];
    }
}
