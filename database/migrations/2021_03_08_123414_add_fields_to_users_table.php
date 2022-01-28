<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('given_names')->nullable()->change();

            $table->date('dob')->nullable()->after('settings');
            $table->string('customer_no')->nullable()->after('settings');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIfExists('dob');
            $table->dropIfExists('customer_no');
        });
    }
}
