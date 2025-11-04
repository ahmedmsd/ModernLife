<?php

namespace Illuminate\Notifications;

trait HasDatabaseNotifications
{

    public function notifications(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }


    public function readNotifications()
    {
        return $this->notifications()->read();
    }


    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }
}
