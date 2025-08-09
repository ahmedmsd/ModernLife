<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('production_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->integer('department_id');
            $table->foreign('department_id')
                ->references('dept_id')
                ->on('departments')
                ->onDelete('cascade');
             $table->integer('assigned_to_employee_id');
            $table->foreign('assigned_to_employee_id')
                ->references('employee_id')
                ->on('employees')
                ->nullable()
                ->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed'])->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('production_tasks');
    }
};