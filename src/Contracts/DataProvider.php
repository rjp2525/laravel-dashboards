<?php

namespace Reno\Dashboard\Contracts;

use Reno\Dashboard\Support\WidgetContext;

interface DataProvider
{
    public function fetch(WidgetContext $context): mixed;
}
