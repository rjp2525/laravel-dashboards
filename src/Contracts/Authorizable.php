<?php

namespace Reno\Dashboard\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface Authorizable
{
    public function authorize(?Authenticatable $user): bool;
}
