<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceComment extends Model
{
    protected $fillable = ['maintenance_request_id','user_id','note'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function (self $comment) {
            $req = $comment->request()->with('requester')->first();
            $author = $comment->user;
            if (!$req || !$req->requester || !$author) return;

            if (method_exists($author, 'hasRole') && $author->hasRole('factory_manager')) {
                Notification::make()
                    ->title('ملاحظة جديدة على طلب الصيانة')
                    ->body("طلب #{$req->id}: {$comment->note}")
                    ->sendToDatabase($req->requester);
            }
        });
    }
}
