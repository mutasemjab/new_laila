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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company');
            $table->string('country');
            $table->string('email')->nullable();
            $table->tinyInteger('gender')->default(1); // 1 male // 2 female
            $table->tinyInteger('category')->default(1); // 1 speaker // 2 participant // 3 Exhibitor // 4 committee // 5 press // 6 other
            $table->string('phone')->unique();
            $table->string('barcode')->unique();
            $table->tinyInteger('activate')->default(1); // 1 yes //2 no
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
