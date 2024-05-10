<?php

namespace Mockrr\Request;

use Mockrr\JsonResource;

class JsonRequest extends Request {
    /**
     * @throws \JsonException
     */
    public function getPayload(): array|string
    {
        // TODO support json content-type
        // Get json body
        $body = file_get_contents('php://input');
        return JsonResource::decode($body);
    }
}
