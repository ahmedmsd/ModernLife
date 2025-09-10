<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'setting_options')) {
                $table->json('setting_options')->nullable()->after('setting_value');
            }
            if (! Schema::hasColumn('system_settings', 'is_sensitive')) {
                $table->boolean('is_sensitive')->default(false)->after('setting_options');
            }
            if (! Schema::hasColumn('system_settings', 'validation_rules')) {
                $table->string('validation_rules')->nullable()->after('is_sensitive');
            }
            if (! Schema::hasColumn('system_settings', 'help_text')) {
                $table->text('help_text')->nullable()->after('validation_rules');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'help_text')) {
                $table->dropColumn('help_text');
            }
            if (Schema::hasColumn('system_settings', 'validation_rules')) {
                $table->dropColumn('validation_rules');
            }
            if (Schema::hasColumn('system_settings', 'is_sensitive')) {
                $table->dropColumn('is_sensitive');
            }
            if (Schema::hasColumn('system_settings', 'setting_options')) {
                $table->dropColumn('setting_options');
            }
        });
    }
};
