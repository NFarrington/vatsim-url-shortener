<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name_first');
            $table->string('name_last');
            $table->string('email')->unique()->nullable();
            $table->boolean('email_verified')->default(0);
            $table->rememberToken();
            $table->json('vatsim_sso_data')->nullable();
            $table->json('vatsim_status_data')->nullable();
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
}
