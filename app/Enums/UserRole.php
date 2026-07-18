<?php

namespace App\Enums;

enum UserRole : string
{
    case ADMIN = 'admin';
    case SELLER = 'seller';
    case SUPPLIER = 'supplier';
}