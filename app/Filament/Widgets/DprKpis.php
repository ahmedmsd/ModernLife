<?php

// app/Filament/Resources/Widgets/DprKpis.php
namespace App\Filament\Widgets;

use App\Models\DepartmentPurchaseRequest;
use Filament\Widgets\StatsOverviewWidget as Widget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DprKpis extends Widget
{
    protected function getStats(): array
    {
        $q = DepartmentPurchaseRequest::query();
        $total      = (clone $q)->count();
        $waitingFac = (clone $q)->where('status','submitted_to_factory')->count();
        $waitingPur = (clone $q)->where('status','sent_to_purchasing')->count();
        $purchased  = (clone $q)->where('status','purchased')->count();
        $delivered  = (clone $q)->where('status','delivered')->count();
        $budget     = (clone $q)->sum('total_estimated_cost');

        return [
            Stat::make('كل الطلبات', (string)$total),
            Stat::make('بانتظار المصنع', (string)$waitingFac),
            Stat::make('بانتظار المشتريات', (string)$waitingPur),
            Stat::make('تم الشراء', (string)$purchased),
            Stat::make('تم التوريد', (string)$delivered),
            Stat::make('إجمالي التكلفة التقديرية', number_format($budget,2).' SAR'),
        ];
    }
}

