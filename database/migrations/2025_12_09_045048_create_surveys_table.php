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
    Schema::create('surveys', function (Blueprint $table) {
        $table->id();
        $table->string('provider_survey_id')->nullable(); // external study ID
        $table->string('survey_name');
        $table->integer('quota_required')->default(0);
        $table->integer('quota_completed')->default(0);
        $table->string('country_language_id')->nullable();
        $table->decimal('cpi', 10, 2)->nullable();
        $table->string('status')->default('live');
        $table->string('live_url')->nullable();
        $table->string('test_url')->nullable();
        $table->integer('incidence')->nullable();
        $table->json('settings')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
