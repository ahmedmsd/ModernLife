<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legacy_client_project_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('legacy_project_id')
                ->constrained('legacy_client_projects')
                ->cascadeOnDelete();

            $table->string('category')->nullable();     // image, agreement, offer, other
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->string('file_path');               // المسار داخل disk
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->index(['legacy_project_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_client_project_files');
    }
};
