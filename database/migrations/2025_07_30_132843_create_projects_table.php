<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_request_id')
                ->constrained()
                ->cascadeOnDelete();

           
            $table->integer('client_id');
            $table->foreign('client_id')
                ->references('client_id')
                ->on('clients')
                ->onDelete('cascade');

            $table->string('project_name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', ['in_progress', 'completed', 'on_hold'])
                ->default('in_progress');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};




