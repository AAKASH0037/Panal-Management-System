<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {

            // PMS internal quota
            $table->integer('internal_quota')
                  ->default(0)
                  ->after('quota_required');

            // CINT quota
            $table->integer('cint_quota')
                  ->default(0)
                  ->after('internal_quota');

            // PMS completed
            $table->integer('internal_completed')
                  ->default(0)
                  ->after('quota_completed');

            // CINT completed
            $table->integer('cint_completed')
                  ->default(0)
                  ->after('internal_completed');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn([
                'internal_quota',
                'cint_quota',
                'internal_completed',
                'cint_completed'
            ]);
        });
    }
};
