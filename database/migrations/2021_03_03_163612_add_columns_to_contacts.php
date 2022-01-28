<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('landline')->nullable()->change();
            $table->string('mobile')->nullable()->change();
            $table->string('job')->nullable()->change();
            $table->boolean('legal')->default(0)->change();

            $table->string('relationship')->nullable()->after('name');
            $table->text('notes')->nullable()->after('job');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('relationship');
            $table->dropColumn('notes');
        });
    }
}
