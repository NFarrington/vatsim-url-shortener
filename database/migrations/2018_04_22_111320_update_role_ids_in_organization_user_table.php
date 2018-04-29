<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoleIdsInOrganizationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('organization_user')->where('role_id', 2)->update(['role_id' => 3]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('organization_user')->where('role_id', 2)->delete();
        DB::table('organization_user')->where('role_id', 3)->update(['role_id' => 2]);
    }
}
