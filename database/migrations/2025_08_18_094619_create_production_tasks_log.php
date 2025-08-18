<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_tasks_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('production_tasks')->cascadeOnDelete();
            $table->string('type'); // created, status_changed, assigned, note_added, file_uploaded, due_changed, ...
            $table->json('data')->nullable();
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('happened_at')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_tasks_log');
    }
};
