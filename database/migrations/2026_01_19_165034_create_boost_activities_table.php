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
        Schema::create('boost_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->integer('duration');
            $table->decimal('budget_per_day', 10, 2)->nullable();
            $table->integer('country_id')->nullable();
            $table->string('boosting_start_date')->nullable();
            $table->string('boosting_end_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('card_id')->nullable();
            $table->enum('status', ['active', 'ended', 'expired', 'completed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boost_activities');
    }
};
