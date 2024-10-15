<?php
// app/Enums/PermissionLevel.php
namespace App\Enums;

enum PermissionLevel: int
{
    case SITEADMIN = 2;
    case COMPANY = 1;
    case USER = 5;
    case BASIC = 8;
    case JOBADMIN = 9;

    public function label(): string
    {
        return match ($this) {
            self::SITEADMIN => 'Site Admin',
            self::COMPANY => 'Company',
            self::USER => 'User',
            self::BASIC => 'Basic',
            self::JOBADMIN => 'Job Admin',


        };
    }
}
