<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\Response;

use apivalk\apivalk\Documentation\Property\AbstractObjectProperty;
use apivalk\apivalk\Documentation\Property\AbstractPropertyCollection;

class ErrorObject extends AbstractObjectProperty
{
    private ?string $errorKey = null;
    private ?string $message = null;

    final public function __construct()
    {
        parent::__construct('error', 'Error');
    }

    public function populate(string $errorKey, string $message): void
    {
        $this->errorKey = $errorKey;
        $this->message = $message;
    }

    public function getErrorKey(): string
    {
        return $this->errorKey;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPropertyCollection(): AbstractPropertyCollection
    {
        return new ErrorObjectPropertyCollection(AbstractPropertyCollection::MODE_VIEW);
    }

    /** @return array{key: string, message: string} */
    public function toArray(): array
    {
        return [
            'key' => $this->errorKey,
            'message' => $this->message,
        ];
    }
}
