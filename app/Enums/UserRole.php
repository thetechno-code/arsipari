<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case OPERATOR = 'operator';
    case VIEWER = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN    => 'Administrator',
            self::OPERATOR => 'Operator',
            self::VIEWER   => 'Viewer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
