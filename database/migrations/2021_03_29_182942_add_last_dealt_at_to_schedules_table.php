<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastDealtAtToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('last_dealt_name')->nullable()->after('current_approved');
            $table->unsignedBigInteger('dealt_by')->nullable()->after('current_approved');
            $table->timestamp('last_dealt_at')->nullable()->after('current_approved');

            $table->foreign('dealt_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('last_dealt_at');
            $table->dropConstrainedForeignId('dealt_by');
        });
    }
}
