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
    Schema::create('qualifications', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('question_id');
        $table->string('logical_operator')->nullable(); // OR, AND
        $table->integer('number_of_required_conditions')->default(1);
        $table->boolean('is_active')->default(true);
        $table->json('pre_codes')->nullable(); // array of precodes
        $table->integer('order')->nullable();
        $table->softDeletes(); // Soft delete
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};
