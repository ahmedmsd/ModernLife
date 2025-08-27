<?php
// app/Models/TaskComment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\User;
use App\Models\ProductionTask;

class TaskComment extends Model
{
    protected $table = 'production_tasks_comments';

    protected $fillable = ['task_id', 'user_id', 'body', 'attachments'];
    protected $casts    = ['attachments' => 'array'];

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id','id');
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function excerpt(): Attribute
    {
        return Attribute::get(fn () => str($this->body)->stripTags()->limit(120));
    }
}
