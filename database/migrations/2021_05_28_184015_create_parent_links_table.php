<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parent_links', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('child_id');
            $table->string('token');
            $table->boolean('linked')->default(0);
            $table->timestamps();

            $table->foreign('child_id')
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
        Schema::dropIfExists('parent_links');
    }
}
