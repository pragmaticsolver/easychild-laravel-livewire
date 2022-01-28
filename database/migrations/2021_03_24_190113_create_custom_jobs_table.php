<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_id');
            $table->nullableMorphs('related');
            $table->string('notification_type');
            $table->json('user_ids');
            $table->json('data')->nullable();
            $table->timestamp('due_at');
            $table->timestamps();

            $table->foreign('auth_id')
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
        Schema::dropIfExists('custom_jobs');
    }
}
