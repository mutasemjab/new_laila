<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_attendance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('day_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();

            // Ensure each user has only one record per room per day
            $table->unique(['user_id', 'day_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room_attendance_metrics');
    }
};
