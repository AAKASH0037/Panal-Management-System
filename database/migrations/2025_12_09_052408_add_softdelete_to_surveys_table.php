<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('surveys', function (Blueprint $table) {
        $table->softDeletes(); // adds deleted_at timestamp column
    });
}

public function down()
{
    Schema::table('surveys', function (Blueprint $table) {
        $table->dropColumn('deleted_at');
    });
}

};
