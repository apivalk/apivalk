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

    public function populate(string $parameter, ValidatorResult $validatorResult): void
    {
        $this->errorKey = $validatorResult->getErrorKey();
        $this->message = $validatorResult->getLocalizedErrorMessage();
        $this->parameter = $parameter;
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
