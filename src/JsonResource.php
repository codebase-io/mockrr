<?php

namespace Mockrr;

use JsonSerializable;
use RuntimeException;
use InvalidArgumentException;

/**
 * Representation of a json resource
 * @package mockrr/mockrr
 */
class JsonResource extends Resource implements JsonSerializable
{
    const TYPE = 'application/json';

    private array $parsed;

    public static function fromFile(string $path): static
    {
        return static::createFromFile(
            $path,
            self::TYPE,
            self::UTF_8,
        );
    }

    public static function fromArray( array $data ): static
    {
        return static::createFromArray($data, self::TYPE, self::UTF_8);
    }

    public static function fromString(string $data): static
    {
        return static::createFromArray(json_decode($data, TRUE), self::TYPE, self::UTF_8);
    }

    public static function fromCallback( callable $fn, ?array $vars=[] ): static
    {
        return static::createFromCallback(
            $fn,
            $vars,
            self::TYPE,
            self::UTF_8,
        );
    }

    public function parse(): static
    {
       if (is_string($this->data)) {
           $parsed = json_decode($this->data, TRUE);
       }

       if (is_object($this->data)) {
           $parsed = get_object_vars($this->data);
       }

        if (is_array($this->data)) {
            $parsed = $this->data;
        }

        if (!isset($parsed)) {
            throw new RuntimeException("Cannot parse data to array for resource.");
        }

        $this->parsed = $parsed;

        return $this;
    }

    public function replace(mixed $override): static
    {
        if (!isset($this->parsed)) {
            throw new RuntimeException("Parse the resource before replacing values.");
        }

        if (!is_array($override)) {
            throw new InvalidArgumentException("Overrides needs to be an array.");
        }

        $this->data = array_merge($this->parsed, $override);

        return $this;
    }

    public function print(): void
    {
        $this->sendHeaders();
        print json_encode($this->data, JSON_PRETTY_PRINT);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->__serialize();
    }
}
