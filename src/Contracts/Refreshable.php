<?php

namespace Reno\Dashboard\Contracts;

use Reno\Dashboard\Support\RefreshConfig;

interface Refreshable
{
    public function refreshStrategy(): RefreshConfig;

    public function refreshInterval(): ?int;
}
