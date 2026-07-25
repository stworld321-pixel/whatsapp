<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip this migration for SQLite - ENUM is not supported and not needed
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // MySQL: ALTER COLUMN to extend the ENUM with new providers
        DB::statement("
            ALTER TABLE sms_provider_configs
            MODIFY COLUMN provider ENUM(
                'twilio','nexmo','messagebird','smsbd','reve','bulksmsbd',
                'sms_dot_bd','mimsms','fast2sms','amazon_sns'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE sms_provider_configs
            MODIFY COLUMN provider ENUM(
                'twilio','nexmo','messagebird','smsbd','reve','bulksmsbd',
                'sms_dot_bd','mimsms'
            ) NOT NULL
        ");
    }
};
