<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUrlAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_analytics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('url_id')->nullable();
            $table->string('request_time')->nullable();
            $table->string('http_host')->nullable();
            $table->string('http_referer')->nullable();
            $table->string('http_user_agent')->nullable();
            $table->string('remote_addr')->nullable();
            $table->string('request_uri')->nullable();
            $table->text('get_data')->nullable();
            $table->text('custom_headers')->nullable();
            $table->integer('response_code')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('url_id')->references('id')->on('urls');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('url_analytics');
    }
}
