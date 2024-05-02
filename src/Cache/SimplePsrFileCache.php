<?php

namespace Mockrr\Cache;

use RuntimeException;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Demonstration implementation of a file cache to avoid including extras
 * @package mockrr/mockrr
 */
class SimplePsrFileCache implements CacheItemPoolInterface
{
    private string $cacheDir;
    public function __construct(?string $dir=NULL)
    {
        $this->cacheDir = $dir ?? sys_get_temp_dir();

        // Should be writable
        if (!is_writable($this->cacheDir)) {
            throw new InvalidArgumentException("Cannot open dir {$this->cacheDir} for writing.");
        }
    }
    /**
     * @inheritDoc
     */
    public function getItem( string $key ): CacheItemInterface
    {
        $fkey     = md5($key);
        $tmpfile = ($this->cacheDir . DIRECTORY_SEPARATOR . $fkey);

        if (file_exists($tmpfile)) {
            $value = unserialize(file_get_contents($tmpfile));
            return new SimplePsrFileCacheItem($key, $value, TRUE);
        }

        return new SimplePsrFileCacheItem($key, NULL);
    }

    /**
     * @inheritDoc
     */
    public function getItems( array $keys = [] ): iterable
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function hasItem( string $key ): bool
    {
        $fkey     = md5($key);
        $tmpfile = ($this->cacheDir . DIRECTORY_SEPARATOR . $fkey);

        return file_exists($tmpfile);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        foreach (glob($this->cacheDir . DIRECTORY_SEPARATOR . '*') as $file) {
            if ($removed = @unlink($file)){
                continue;
            }

            return FALSE;
        }

        return TRUE;
    }

    /**
     * @inheritDoc
     */
    public function deleteItem( string $key ): bool
    {
        $key     = md5($key);
        $tmpfile = ($this->cacheDir . DIRECTORY_SEPARATOR . $key);

        return @unlink($tmpfile);
    }

    /**
     * @inheritDoc
     */
    public function deleteItems( array $keys ): bool
    {
        foreach ($keys as $key) {
            $deleted = $this->deleteItem($key);
            if (!$deleted) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @inheritDoc
     */
    public function save( CacheItemInterface $item ): bool
    {
        $key     = md5($item->getKey());
        $tmpfile = ($this->cacheDir . DIRECTORY_SEPARATOR . $key);
        $written = file_put_contents($tmpfile, serialize($item->get()));

        if (FALSE === $written) {
            throw new RuntimeException("Error while writing to file {$tmpfile}.");
        }

        return TRUE;
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred( CacheItemInterface $item ): bool
    {
        throw new RuntimeException("not implemented.");
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        throw new RuntimeException("not implemented.");
    }
}
