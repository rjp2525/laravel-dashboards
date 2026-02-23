<?php

namespace Reno\Dashboard\Contracts;

interface Filterable
{
    /** @return array<string, mixed> */
    public function filters(): array;

    /** @param array<string, mixed> $filters */
    public function applyFilters(mixed $query, array $filters): void;
}
