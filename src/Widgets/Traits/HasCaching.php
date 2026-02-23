<?php

namespace Reno\Dashboard\Widgets\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

trait HasCaching
{
    public function cacheTtl(): int
    {
        /** @var int $ttl */
        $ttl = config('dashboard.cache.default_ttl', 300);

        return $ttl;
    }

    public function cacheKey(): string
    {
        /** @var string $prefix */
        $prefix = config('dashboard.cache.prefix', 'dashboard');

        return "{$prefix}:widget:{$this->key()}";
    }

    public function buildCacheKey(string ...$segments): string
    {
        return $this->cacheKey().':'.implode(':', $segments);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function getCached(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        if (! config('dashboard.cache.enabled', true)) {
            return $callback();
        }

        /** @var string|null $storeName */
        $storeName = config('dashboard.cache.store');
        $store = Cache::store($storeName);
        $ttl = $ttl ?? $this->cacheTtl();

        return $store->remember($key, $ttl, $callback);
    }

    public function forgetCache(string ...$keys): void
    {
        /** @var string|null $storeName */
        $storeName = config('dashboard.cache.store');
        $store = Cache::store($storeName);

        foreach ($keys as $key) {
            $store->forget($key);
        }
    }
}
