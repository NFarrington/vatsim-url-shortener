<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatsimConnectUserFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->text('vatsim_connect_access_token')->nullable()->after('remember_token');
                $table->text('vatsim_connect_refresh_token')->nullable()->after('vatsim_connect_access_token');
                $table->dateTime('vatsim_connect_token_expiry')->nullable()->after('vatsim_connect_refresh_token');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'users',
            function (Blueprint $table) {
                $table->dropColumn(
                    [
                        'vatsim_connect_access_token',
                        'vatsim_connect_refresh_token',
                        'vatsim_connect_token_expiry',
                    ]
                );
            }
        );
    }
}
