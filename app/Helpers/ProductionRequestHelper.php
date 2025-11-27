<?php

namespace App\Helpers;

use App\Enums\ProductionRequestStatus;

class ProductionRequestHelper
{
    public static function statusColor(ProductionRequestStatus|string|null $status): string
    {
        $value = $status instanceof ProductionRequestStatus
            ? $status->value
            : $status;

        return match ($value) {
            'draft'        => '#6b7280',
            'submitted'    => '#3b82f6',
            'under_review' => '#f59e0b',
            'approved'     => '#10b981',
            'rejected'     => '#ef4444',
            default        => '#9ca3af',
        };
    }
}
