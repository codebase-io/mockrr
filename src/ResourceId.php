<?php

namespace Mockrr;

use Stringable;

class ResourceId implements Stringable
{
    private readonly string $key;
    private function __construct(string $key)
    {
        $this->key = md5($key);
    }

    public function __toString(): string
    {
        return $this->key;
    }

    public static function from(mixed ...$ids): self
    {
        return new self(join('', array_map(fn(mixed $v) => is_scalar($v) ? $v : serialize($v), $ids)));
    }
}
