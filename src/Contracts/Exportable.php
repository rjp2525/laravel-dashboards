<?php

namespace Reno\Dashboard\Contracts;

interface Exportable
{
    public function exportAs(string $format): mixed;
}
