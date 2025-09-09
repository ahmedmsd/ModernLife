<?php
// app/Models/Concerns/HasStatusScopes.php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasStatusScopes
{

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['completed']);

    }
}
