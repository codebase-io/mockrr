<?php

namespace Mockrr\Request;

abstract class Request {

    public function __construct(private array $request) {}

    public static function fromGlobals(): static
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? 'text/plain';
        // TODO support handlers for content type
    }

    public function getPayload(): array|string
    {
        // TODO abstract method
    }
}
