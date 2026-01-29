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
        if (!Schema::hasColumn('clients', 'zoho_contact_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('zoho_contact_id')->nullable()->after('zoho_account_id');
                $table->unique('zoho_contact_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('zoho_contact_id');
        });
    }
};
