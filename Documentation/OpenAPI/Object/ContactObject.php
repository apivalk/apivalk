<?php

declare(strict_types=1);

namespace apivalk\apivalk\Documentation\OpenAPI\Object;

/**
 * Class ContactObject
 *
 * @see https://swagger.io/specification/#contact-object
 *
 * @package apivalk\apivalk\Documentation\OpenAPI\Object
 */
class ContactObject implements ObjectInterface
{
    /** @var string */
    private $name;
    /** @var string */
    private $url;
    /** @var string */
    private $email;

    public function __construct(string $name, string $url, string $email)
    {
        $this->name = $name;
        $this->url = $url;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'url' => $this->url,
            'email' => $this->email
        ];
    }
}
