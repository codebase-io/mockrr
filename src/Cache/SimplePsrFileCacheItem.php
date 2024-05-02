<?php

namespace Mockrr\Cache;

use DateInterval;
use DateTimeInterface;
use RuntimeException;
use Psr\Cache\CacheItemInterface;

/**
 * Demonstration implementation of a file cache item
 * @package mockrr/mockrr
 */
class SimplePsrFileCacheItem implements CacheItemInterface {

    public function __construct(
        private string $key,
        private mixed $value,
        private bool $isHit=FALSE
    ) {}

    /**
     * @inheritDoc
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function get(): mixed {
        return $this->value ?? NULL;
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool {
        return $this->isHit;
    }

    /**
     * @inheritDoc
     */
    public function set( mixed $value ): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt( ?DateTimeInterface $expiration ): static
    {
        throw new RuntimeException("Expiry is not supported");
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter( DateInterval|int|null $time ): static
    {
        throw new RuntimeException("Expiry is not supported");
    }
}
