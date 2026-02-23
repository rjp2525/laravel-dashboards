<?php

namespace Reno\Dashboard\Enums;

enum AclDriver: string
{
    case POLICY = 'policy';
    case SPATIE = 'spatie';
    case CUSTOM = 'custom';
}
