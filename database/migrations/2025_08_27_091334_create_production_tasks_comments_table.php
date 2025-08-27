<?php

// database/migrations/2025_08_27_000000_create_task_comments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('production_tasks_comments', function (Blueprint $table) {
            $table->id();
            // غيّر اسم الجدول/العمود أدناه لو موديلك ProductionTask وجدوله production_tasks
            $table->foreignId('task_id')->constrained('production_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            // سنحفظ أسماء الملفات/المرفقات كـ JSON (اختياري)
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('production_tasks_comments');
    }
};
