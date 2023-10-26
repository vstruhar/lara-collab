<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case DEVELOPER = 'developer';
    case DESIGNER = 'designer';
    case CLIENT = 'client';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::DEVELOPER => 'Developer',
            self::DESIGNER => 'Designer',
            self::CLIENT => 'Client',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
