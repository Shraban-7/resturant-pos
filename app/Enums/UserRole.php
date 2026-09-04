<?php

namespace App\Enums;

enum UserRole : string
{
    case ADMIN = 'admin';
    case EMPLOYEE = 'employee';

    /** @deprecated Pre-RBAC roles. Never assign; only read for old rows. */
    case SELLER = 'seller';

    /** @deprecated Pre-RBAC roles. Never assign; only read for old rows. */
    case SUPPLIER = 'supplier';

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}