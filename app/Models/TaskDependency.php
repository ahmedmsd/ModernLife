<?php

// app/Models/TaskDependency.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    protected $table = 'production_task_dependencies';

    public const KIND_HARD_BLOCK = 'hard_block';
    public const KIND_SOFT_SEQUENCE = 'soft_sequence';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'kind',
        'note',
    ];

    /* ===================== العلاقات ===================== */

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'depends_on_task_id');
    }

    /* ===================== Scopes مفيدة ===================== */

    public function scopeHardBlock($query)
    {
        return $query->where('kind', self::KIND_HARD_BLOCK);
    }

    public function scopeSoftSequence($query)
    {
        return $query->where('kind', self::KIND_SOFT_SEQUENCE);
    }
}
