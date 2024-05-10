<?php

namespace Mockrr;

use RuntimeException;
use Mockrr\Request\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

// TODO
// - support for retrieving json request body
// - support for retrieving xml and graphql resources

/**
 * Package utility class
 * @see Resource
 * @see Request
 * @see ../examples/index.php
 * @package mockrr/mockrr
 */
class Mockrr {

    public static string $include_path = __DIR__;

    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {}

    public static function set_include_path(string $path): void
    {
        if (!is_dir($path)) {
            throw new RuntimeException("Include path should be a directory.");
        }

        Mockrr::$include_path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function cached(string $key): ?Resource
    {
        if ($this->cache->hasItem($key)) {
            $item = $this->cache->getItem($key);
            return $item->get();
        }

        return NULL;
    }

    // Persist resource under key

    /**
     * @throws InvalidArgumentException
     */
    public function cache(Resource $resource, string $key=NULL): void
    {
        $item = $this->cache->getItem($key)->set($resource);
        $this->cache->save($item);
    }

    public function generate(mixed $resource, ?string $type=Resource::DTYPE, ?string $charset=Resource::UTF_8): Resource
    {
        if (is_a($resource, ResourceInterface::class)) {
            /** @var ResourceInterface $resource */
            return $resource;
        }

        /** @var ResourceInterface $handler */
        $handler = Resource::getTypeHandler($type);

        switch (TRUE) {

            case is_array($resource) :
                return $handler::fromArray($resource);

            case is_callable($resource) :
                return $handler::fromCallback($resource, [$type, $charset]);

            case is_readable($resource) :
                return $handler::fromFile($resource);

            case is_object($resource) :
                return $handler::fromArray(get_object_vars($resource));

            case is_scalar($resource) :
                return $handler::fromString($resource);
        }

        throw new RuntimeException("Could not generate resource from input.");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function once( string $id, mixed $resource ): Resource
    {
        if ($r = $this->cached( $id )) {
            return $r;
        }

        $r = $this->generate($resource);
        $this->cache($r, $id);

        return $r;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function sequence(string $id, string $seq, array $resources) : Resource
    {
        if ($r = $this->cached( $id )) {
            // Resource is cached
            return $r;
        }

        $item    = $this->cache->getItem("seq_idx_{$seq}");
        $seq_idx = $item->isHit() ? $item->get() : 0;
        $size    = sizeof($resources);
        $index   = array_keys($resources)[$seq_idx];

        if ($seq_idx < $size) {
            $next   = $resources[$index];
            $seq_idx= ($seq_idx+1) >= $size ? 0 : array_keys($resources)[++$seq_idx];
        }
        else{
            throw new RuntimeException("Out of bounds index {$index}.");
        }

        $this->cache->save($item->set($seq_idx));

        return $this->once($id, $next);

    }

    /**
     * @throws InvalidArgumentException
     */
    public function update(string $id, mixed $data=[]): Resource
    {
        $resource = $this->cached( $id ) ?? $this->generate($data, $data['type'] ?? Resource::DTYPE, $data['charset'] ?? Resource::UTF_8);
        $resource = $resource->replace($data);

        $this->cache($resource, $id);

        return $resource;
    }

    public function request(): Request
    {
        // TODO...
    }
}
