<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** إنشاء جدول طلبات الصيانة */
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('client_id');

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->date('request_date')->index();

            $table->text('details')->nullable();
            $table->json('images')->nullable(); // يخزن مسارات الصور

            $table->enum('status', ['new','in_progress','completed','cancelled'])->default('new')->index();
            $table->string('current_owner_role')->nullable();
            $table->unsignedBigInteger('current_owner_user_id')->nullable();
            $table->timestamp('sent_to_owner_at')->nullable();
            $table->timestamp('received_by_owner_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
