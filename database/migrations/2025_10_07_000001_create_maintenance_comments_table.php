<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** تعليقات على طلبات الصيانة (ملاحظات مدير المصنع/الفريق) */
    public function up(): void
    {
        Schema::create('maintenance_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_request_id');
            $table->unsignedBigInteger('user_id')->nullable(); // كاتب التعليق
            $table->text('note');                               // نص الملاحظة
            $table->timestamps();

            $table->foreign('maintenance_request_id')
                ->references('id')->on('maintenance_requests')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_comments');
    }
};
