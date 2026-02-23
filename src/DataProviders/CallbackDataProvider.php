<?php

namespace Reno\Dashboard\DataProviders;

use Closure;
use Reno\Dashboard\Contracts\DataProvider;
use Reno\Dashboard\Support\WidgetContext;

class CallbackDataProvider implements DataProvider
{
    public function __construct(
        protected Closure $callback,
    ) {}

    public static function from(Closure $callback): self
    {
        return new self($callback);
    }

    public function fetch(WidgetContext $context): mixed
    {
        return ($this->callback)($context);
    }
}
