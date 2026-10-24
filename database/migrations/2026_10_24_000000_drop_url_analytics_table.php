<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUrlAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('url_analytics');
    }

    /**
     * Reverse the migrations.
     *
     * Recreates the table empty. The dropped rows aren't restored.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('url_analytics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('url_id')->nullable()->index();
            $table->string('request_time')->nullable();
            $table->text('http_host')->nullable();
            $table->text('http_referer')->nullable();
            $table->text('http_user_agent')->nullable();
            $table->string('remote_addr')->nullable();
            $table->string('request_uri')->nullable();
            $table->longText('get_data')->nullable();
            $table->longText('custom_headers')->nullable();
            $table->integer('response_code')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }
}
