<?php

namespace Squareetlabs\LaravelSimplePermissions\Enums;

enum AccessLevel: int
{
    case DEFAULT = 0;
    case FORBIDDEN = 1;
    case ROLE_ALLOWED = 2;
    case ROLE_FORBIDDEN = 3;
    case GROUP_ALLOWED = 4;
    case GROUP_FORBIDDEN = 5;
    case USER_ALLOWED = 5;
    case USER_FORBIDDEN = 6;
    case GLOBAL_ALLOWED = 6;
}

