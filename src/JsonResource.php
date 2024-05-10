<?php

namespace Mockrr;

use InvalidArgumentException;
use JsonException;
use JsonSerializable;
use RuntimeException;

/**
 * Representation of a json resource
 * @package mockrr/mockrr
 */
class JsonResource extends Resource implements JsonSerializable
{
    const TYPE = 'application/json';

    /**
     * @throws JsonException
     */
    public static function decode(string $json): array|string
    {
        $trimmed = trim($json);
        if ( !str_starts_with( $trimmed, "[" ) && !str_starts_with($trimmed, "{") && !str_starts_with($trimmed, '"')){
            // convert to json string
            $json = '"'. $json .'"';
        }
        return json_decode($json, TRUE, 256, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function fromFile(string $path): static
    {
        $resource = static::createFromFile(
            $path,
            self::TYPE,
            self::UTF_8,
        );

        $resource->data = self::decode($resource->data);

        return $resource;
    }

    public static function fromArray( array $data ): static
    {
        return static::createFromArray($data, self::TYPE, self::UTF_8);
    }

    /**
     * @throws JsonException
     */
    public static function fromString(string $data): static
    {
        $decoded = self::decode($data);
        if (is_array($decoded)) {
            return static::createFromArray($decoded, self::TYPE, self::UTF_8);
        }

        return static::createFromString($decoded, self::TYPE, self::UTF_8);
    }

    /**
     * @throws JsonException
     */
    public static function fromCallback( callable $fn, ?array $vars=[] ): static
    {
        $resource = static::createFromCallback(
            $fn,
            $vars,
            self::TYPE,
            self::UTF_8,
        );

        if (is_scalar($resource->data)) {
            $resource->data = self::decode($resource->data);
        }

        return $resource;
    }

    public function toArray(): array
    {
        if (!is_array($this->data)) {
            throw new RuntimeException("Resource data cannot be converted to array. Use replaceFn for replacing values.");
        }

        return $this->data;
    }

    /**
     * @throws JsonException
     */
    public function replace(mixed $override): static
    {
        if (is_a($override, ResourceInterface::class)) {
            return $override;
        }

        if (is_callable($override)){
            $replace   = self::fromCallback($override, ['cached'=> $this->data]);
            $this->data= array_merge($this->toArray(), (array) $replace );
            return $this;
        }

        if (is_scalar($this->data)) {
            // Replace entire value on scalars
            $this->data = self::decode($override);
            return $this;
        }

        if (is_array($override)) {
            $this->data = array_replace_recursive($this->toArray(), $override);
            return $this;
        }

        throw new InvalidArgumentException("Cannot replace resource using input.");
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
