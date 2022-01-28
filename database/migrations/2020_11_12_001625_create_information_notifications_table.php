<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformationNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('information_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('information_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('information_id')
                ->references('id')
                ->on('informations');

            $table->foreign('user_id')
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
        Schema::dropIfExists('information_notifications');
    }
}
