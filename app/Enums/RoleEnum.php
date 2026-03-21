<?php

namespace App\Enums;

enum RoleEnum: string
{
    case OWNER = 'Owner';
    case ADMIN = 'Admin';
    case MEMBER = 'Member';
}