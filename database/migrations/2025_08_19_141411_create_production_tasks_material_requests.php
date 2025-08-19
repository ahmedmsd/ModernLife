<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('production_tasks_material_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('production_tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->enum('status', ['requested','fulfilled','cancelled'])->default('requested');        // requested|fulfilled|cancelled
            $table->string('po_number')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('provided_by')->nullable();
            $table->timestamp('provided_at')->nullable();
            $table->timestamps();

            $table->index(['task_id','status']);
            $table->index('requested_at');
        });
    }
    public function down(): void {
        Schema::dropIfExists('production_tasks_material_requests');
    }
};
