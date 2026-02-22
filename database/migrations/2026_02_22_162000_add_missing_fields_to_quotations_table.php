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
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'adjustment')) {
                $table->decimal('adjustment', 15, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('quotations', 'contract_type')) {
                $table->string('contract_type')->nullable()->after('zoho_module');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['adjustment', 'contract_type']);
        });
    }
};
