<?php

namespace Mockrr;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;

class Mockrr {

    private array $generated = [];

    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {}

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function cached(string $key): ?Resource
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

    public function generate(mixed $resource, ?string $type=Resource::DTYPE, ?string $charset=Resource::UTF_8): void
    {
        if (is_a($resource, ResourceInterface::class)) {
            /** @var ResourceInterface $resource */
            ($this->generated[] = $resource) && $resource->print();
            return;
        }

        /** @var ResourceInterface $handler */
        $handler = Resource::getTypeHandler($type);

        switch (TRUE) {

            case is_array($resource) :
                $this->generate($handler::fromArray($resource));
                return;

            case is_callable($resource) :
                $this->generate($handler::fromCallback($resource, [$type, $charset]));
                return;

            case is_readable($resource) :
                $this->generate($handler::fromFile($resource));
                return;

            case is_object($resource) :
                $this->generate($handler::fromArray(get_object_vars($resource)));
                return;
        }

        throw new RuntimeException("Could not generate resource from input.");
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function once( string $id, mixed $resource ): void
    {
        if ($r = $this->cached( $id )) {
            $this->generate($r);
            return;
        }

        $this->generate($resource);
        $this->cache(end($this->generated), $id);
    }

    public function sequence($id, $seq, array $resources) : void
    {
        // TODO next in sequence
        // we need to identify the sequence and the current id
    }

    public function update(string $id, array $update=[]): Resource
    {
        // TODO ...
    }
}
