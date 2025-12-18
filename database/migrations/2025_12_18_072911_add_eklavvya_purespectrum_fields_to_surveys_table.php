<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {


            $table->integer('eklavvya_completed')
                  ->default(0)
                  ->after('cint_completed');

            $table->integer('purespectrum_completed')
                  ->default(0)
                  ->after('eklavvya_completed');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn([
            
                'eklavvya_completed',
                'purespectrum_completed',
            ]);
        });
    }
};
