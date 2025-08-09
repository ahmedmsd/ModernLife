<?php
// app/Helpers/ProductionRequestHelper.php
namespace App\Helpers;

use App\Enums\ProductionRequestStatus;

class ProductionRequestHelper {
    public static function statusColor(ProductionRequestStatus|string|null $status): string {
        function getStatusColor($status): string {
            return match ($status?->value ?? $status) {
                'draft' => '#6b7280', // رمادي
                'submitted' => '#3b82f6', // أزرق
                'under_review' => '#f59e0b', // أصفر
                'approved' => '#10b981', // أخضر
                'rejected' => '#ef4444', // أحمر
                default => '#9ca3af',     // رمادي افتراضي
            };
        }
    }
}
