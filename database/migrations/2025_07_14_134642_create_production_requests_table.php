<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('production_requests', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->text('project_description')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('showroom_id');
            $table->string('agreement_file')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('production_request_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_request_id');
            $table->unsignedBigInteger('department_id');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('production_request_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_request_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('action', ['approved', 'rejected']);
            $table->text('note')->nullable();
            $table->timestamp('action_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_request_logs');
        Schema::dropIfExists('production_request_files');
        Schema::dropIfExists('production_requests');
    }
};
