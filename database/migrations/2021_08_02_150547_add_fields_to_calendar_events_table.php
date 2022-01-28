<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCalendarEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('groups');
            $table->boolean('birthday')->default(false)->after('title');
            $table->unsignedBigInteger('birthday_id')->nullable()->after('birthday');

            $table->foreign('birthday_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('roles');
            $table->dropColumn('birthday');
            $table->dropColumn('birthday_id');
        });
    }
}
