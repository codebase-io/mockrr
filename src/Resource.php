<?php

namespace Mockrr;

use RuntimeException;
use InvalidArgumentException;

abstract class Resource implements ResourceInterface
{
    const DTYPE = 'application/json';

    const UTF_8 = 'utf-8';

    const STATUS_OK = 200;
    const STATUS_NOT_FOUND = 404;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_SERVER_ERROR = 500;

    protected int $status = 200;
    protected array $headers = [];

    public function __construct(
        protected mixed $data,
        protected string $type = self::DTYPE,
        protected string $charset = self::UTF_8,
    ) {
        $this->headers['Content-Type'] = "{$this->type}; charset={$this->charset}";
    }

    public function setStatus(int $code): static
    {
        $this->status = $code;
        return $this;
    }

    protected static function createFromFile(string $path, $type, $charset): static
    {
        $path = is_readable($path) ? $path : (Mockrr::$include_path . $path);

        if (is_readable($path)) {
            return static::createFromString(file_get_contents($path), $type, $charset);
        }

        throw new RuntimeException("Could not read file from {$path}.");
    }

    protected static function createFromCallback(
        callable|string $fn,
        array $vars=[],
        $type = NULL,
        $charset = NULL,
    ): static
    {
        $data = call_user_func($fn, $vars, $type ?? static::DTYPE, $charset ?? static::UTF_8);
        return new static($data, $type, $charset);
    }

    protected static function createFromArray(array $data, $type, $charset): static
    {
        return new static($data, $type, $charset);
    }

    protected static function createFromString(string $data, $type, $charset): static
    {
        return new static($data, $type, $charset);
    }

    public function serialize(): string
    {
        return serialize($this->data);
    }

    protected function sendHeaders(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name=> $value) {
            header("$name: $value");
        }
    }

    public function addHeader($name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function unserialize( string $data ): void
    {
        $data = unserialize($data);

        $this->data    = $data['data'];
        $this->type    = $data['type'];
        $this->charset = $data['charset'];
        $this->headers = $data['headers'];
    }

    public function __serialize(): array
    {
        return [
            'data'    => $this->data,
            'type'    => $this->type,
            'charset' => $this->charset,
            'headers' => $this->headers,
        ];
    }

    public function __unserialize( array $data ): void
    {
        $this->data    = $data['data'];
        $this->type    = $data['type'];
        $this->charset = $data['charset'];
        $this->headers = $data['headers'];
    }

    public static function getTypeHandler(string $type): string
    {
        return match ($type) {
            JsonResource::TYPE => JsonResource::class,

            default =>
                throw new InvalidArgumentException("Cannot handle resource of type {$type}.")
        };
    }
}
