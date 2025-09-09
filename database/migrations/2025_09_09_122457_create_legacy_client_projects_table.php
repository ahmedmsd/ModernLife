<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legacy_client_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');

            $table->string('project_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->text('details')->nullable();

            $table->timestamps();

            $table->foreign('client_id')
                ->references('client_id')->on('clients')
                ->cascadeOnDelete();

            $table->index(['client_id', 'start_date']);
            $table->index(['client_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_client_projects');
    }
};
