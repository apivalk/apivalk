<?php

declare(strict_types=1);

namespace apivalk\apivalk\Cache;

class CacheItem
{
    /** @var int|null */
    private $ttl;
    /** @var string */
    private $key;
    /** @var mixed */
    private $value;
    /** @var \DateTime */
    private $createdAt;

    /**
     * @param mixed          $value
     * @param \DateTime|null $createdAt if you set createdAt, it will be used instead of the current time. Make sure it uses UTC as timezone
     */
    public function __construct(string $key, $value, ?int $ttl = null, ?\DateTime $createdAt = null)
    {
        $this->key = $key;
        $this->ttl = $ttl;
        $this->value = $value;

        if ($createdAt !== null) {
            $this->createdAt = $createdAt;
        } else {
            $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?\DateTime
    {
        if ($this->ttl === null) {
            return null;
        }

        return $this->createdAt->modify(\sprintf('+%d seconds', $this->ttl));
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public static function byJson(string $json): ?self
    {
        $array = json_decode($json, true);

        if (!\is_array($array)
            || !\array_key_exists('key', $array)
            || !\array_key_exists('ttl', $array)
            || !\array_key_exists('value', $array)
            || !\array_key_exists('createdAt', $array)) {
            return null;
        }

        /** @var array{key: string, ttl: int|null, value: mixed, createdAt: string} $array */

        return new self(
            $array['key'],
            $array['value'],
            $array['ttl'],
            \DateTime::createFromFormat(
                CacheInterface::CREATED_AT_FORMAT,
                $array['createdAt'],
                new \DateTimeZone('UTC')
            )
        );
    }

    public function toJson(): string
    {
        return json_encode(
            [
                'key' => $this->key,
                'ttl' => $this->ttl,
                'value' => $this->value,
                'createdAt' => $this->createdAt->format(CacheInterface::CREATED_AT_FORMAT),
            ]
        );
    }
}
