<?php

namespace Reno\Dashboard\Contracts;

interface Cacheable
{
    public function cacheTtl(): int;

    public function cacheKey(): string;
}
