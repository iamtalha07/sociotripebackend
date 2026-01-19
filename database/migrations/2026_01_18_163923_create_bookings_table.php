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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('provider_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->enum('booking_day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->string('booking_date');
            $table->string('arrival_time');
            $table->decimal('price_ht', 10, 2)->nullable();
            $table->decimal('vat', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->enum('status', ['request', 'completed', 'cancel', 'disputed', 'active', 'rejected'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
