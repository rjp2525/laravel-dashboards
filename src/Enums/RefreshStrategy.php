<?php

namespace Reno\Dashboard\Enums;

enum RefreshStrategy: string
{
    case POLL = 'poll';
    case PUSH = 'push';
    case INERTIA = 'inertia';
    case MANUAL = 'manual';
}
