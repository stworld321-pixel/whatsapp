<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_provider_configs')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `ai_provider_configs` MODIFY `provider` ENUM('openai', 'anthropic', 'gemini') NOT NULL");

            if (Schema::hasColumn('ai_provider_configs', 'default_model_chat')) {
                DB::statement('ALTER TABLE `ai_provider_configs` MODIFY `default_model_chat` VARCHAR(128) NULL');
            }

            if (Schema::hasColumn('ai_provider_configs', 'default_model_embed')) {
                DB::statement('ALTER TABLE `ai_provider_configs` MODIFY `default_model_embed` VARCHAR(128) NULL');
            }
        }

        Schema::table('ai_provider_configs', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_provider_configs', 'default_model_chat')) {
                $table->string('default_model_chat', 128)->nullable()->after('credentials');
            }

            if (! Schema::hasColumn('ai_provider_configs', 'default_model_embed')) {
                $table->string('default_model_embed', 128)->nullable()->after('default_model_chat');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_provider_configs')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `ai_provider_configs` MODIFY `provider` ENUM('openai', 'anthropic') NOT NULL");
        }
    }
};
