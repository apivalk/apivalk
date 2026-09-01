<?php

declare(strict_types=1);

namespace Tests\Integration\RealWorld\Customer\Document;

use apivalk\apivalk\Documentation\ApivalkResponseDocumentation;
use apivalk\apivalk\Http\Response\AbstractApivalkResponse;

class DocumentUploadedResponse extends AbstractApivalkResponse
{
    /** @var array<string, mixed> */
    private array $document;

    /** @param array<string, mixed> $document */
    public function __construct(array $document)
    {
        $this->document = $document;
    }

    public static function getDocumentation(): ApivalkResponseDocumentation
    {
        $doc = new ApivalkResponseDocumentation();
        $doc->setDescription('Document stored response');

        return $doc;
    }

    public static function getStatusCode(): int
    {
        return self::HTTP_201_CREATED;
    }

    public function toArray(): array
    {
        return ['data' => $this->document];
    }
}
