<?php

namespace Mockrr;

use Serializable;

interface ResourceInterface extends Serializable
{
    /**
     * Create resource from array input
     *
     * @param array $data
     *
     * @return static
     */
    public static function fromArray(array $data): static;
    public static function fromFile(string $path): static;
    public static function fromCallback(callable $fn, ?array $vars): static;
    public static function fromString(string $data): static;

    public function setStatus(int $code): static;

    public function addHeader($name, string $value): void;

    public function replace(mixed $override): static;

    public function print(): void;
}
