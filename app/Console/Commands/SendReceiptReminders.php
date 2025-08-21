<?php

namespace App\Console\Commands;

use App\Models\ProductionRequest;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SendReceiptReminders extends Command
{
    protected $signature = 'workflow:remind-receipts';
    protected $description = 'Send reminders for production requests pending receipt for >= 3 days.';

    public function handle(): int
    {
        $threshold = now()->subDays(3);

        $rows = ProductionRequest::query()
            ->where('phase_status', 'pending')
            ->whereNotNull('current_owner_role')
            ->whereNotNull('sent_to_owner_at')
            ->where('sent_to_owner_at', '<=', $threshold)
            ->limit(500)
            ->get(['id','current_owner_role','sent_to_owner_at']);

        $count = 0;
        foreach ($rows as $pr) {
            try {
                $role = Role::findByName($pr->current_owner_role);
                foreach ($role->users as $user) {
                    Notification::make()
                        ->title('تنبيه: تأخر تأكيد الاستلام')
                        ->body("طلب التصنيع رقم {$pr->id}: مضت 3 أيام دون تأكيد استلام. يرجى التأكيد.")
                        ->sendToDatabase($user);
                }
                $count++;
            } catch (\Throwable $e) {
                // تجاهل دور غير موجود
            }
        }

        $this->info("Reminders sent for {$count} requests.");
        return self::SUCCESS;
    }
}
