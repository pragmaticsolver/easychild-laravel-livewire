<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('given_names')->index();
            $table->string('last_name')->index();
            $table->enum('role', [
                'Admin',
                'Manager',
                'Parent',
                'Principal',
                'User',
                'Vendor',
            ])->index();
            $table->string('email')->unique();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('token')->unique()->nullable();
            $table->json('settings')->nullable();
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->softDeletes();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations');

            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
