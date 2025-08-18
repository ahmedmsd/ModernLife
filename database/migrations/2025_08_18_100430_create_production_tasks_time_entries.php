<?php

// database/migrations/2025_08_18_000002_create_task_time_entries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_tasks_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('production_tasks')->cascadeOnDelete();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_sec')->nullable(); // يُملأ عند الإنهاء
            $table->string('reason')->nullable(); // optional: سبب الإيقاف/الاستئناف
            $table->timestamps();

            $table->index(['task_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_tasks_time_entries');
    }
};

