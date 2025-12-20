<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Response;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Documentation\Property\StringProperty;

class InternalServerErrorApivalkResponse extends AbstractApivalkResponse
{
    /** @var string */
    private $errorMessage = 'We\'ve run into an unknown error, please try again later.';
    /** @var array */
    private $context = [];

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        $responseDocumentation = new ApivalkResponseDocumentation();

        $responseDocumentation->setDescription('Internal server error');
        $responseDocumentation->addProperty(new StringProperty('error', 'Error message'));

        return $responseDocumentation;
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_500_INTERNAL_SERVER_ERROR;
    }

    public function setErrorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function toArray(): array
    {
        $response = [
            'error' => $this->errorMessage,
        ];

        if (!empty($this->context)) {
            $response['context'] = $this->context;
        }

        return $response;
    }
}
