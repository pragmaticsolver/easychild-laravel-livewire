<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('date')->index();
            $table->time('start')->nullable();
            $table->time('end')->nullable();
            $table->time('presence_start')->nullable();
            $table->time('presence_end')->nullable();
            $table->json('eats_onsite')->nullable();
            $table->boolean('available')->default(true);
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->text('allergy')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

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
        Schema::dropIfExists('schedules');
    }
}
