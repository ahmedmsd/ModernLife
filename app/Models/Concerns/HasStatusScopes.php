<?php
// app/Models/Concerns/HasStatusScopes.php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasStatusScopes
{

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'rejected', 'completed', 'cancelled']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['approved', 'rejected', 'completed', 'cancelled']);
    }
}
