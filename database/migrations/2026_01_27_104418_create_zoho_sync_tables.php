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
        if (!Schema::hasColumn('clients', 'zoho_account_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('zoho_account_id')->nullable()->after('client_id');
                $table->unique('zoho_account_id');
            });
        }

        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->id();
                $table->string('zoho_quote_id')->unique();
                $table->string('subject')->nullable();
                $table->string('quote_number')->nullable();
                $table->string('quote_stage')->nullable();
                $table->date('valid_till')->nullable();
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('sub_total', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->decimal('discount', 15, 2)->default(0);
                $table->integer('client_id')->nullable();
                $table->json('raw_data')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('zoho_account_id');
        });
    }
};
