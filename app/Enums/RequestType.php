<?php

namespace App\Enums;

enum RequestType: string {
    case Direct = 'direct';
    case Indirect = 'indirect';
    public function label(): string {
        return match($this) {
            self::Direct => 'طلب مباشر',
            self::Indirect => 'طلب غير مباشر',

        };
    }
}
