<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('birthday');
            $table->string('pronoun');
            $table->string('looking');
            $table->string('education')->nullable();
            $table->string('job')->nullable();
            $table->string('location')->nullable();
            $table->integer('height')->nullable();
            $table->boolean('notifications')->default(1);
            $table->integer('distance')->nullable();
            $table->string('age_range')->default('18-60');
            $table->tinyInteger('sign');
            $table->tinyInteger('agrees_count')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_tokens', function (Blueprint $table) {
           $table->bigIncrements('id');
           $table->unsignedBigInteger('user_id');
           $table->string('device_id');
           $table->string('token');
           $table->timestamp('expires_at');
           $table->timestamps();

           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('user_photos', function (Blueprint $table) {
           $table->bigIncrements('id');
           $table->unsignedBigInteger('user_id');
           $table->string('file');
           $table->timestamps();

           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });

        Schema::create('user_quiz', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');

            $table->boolean('answer_1')->default(0);
            $table->boolean('answer_2')->default(0);
            $table->boolean('answer_3')->default(0);
            $table->boolean('answer_4')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_quiz');
        Schema::dropIfExists('user_photos');
        Schema::dropIfExists('user_tokens');

        Schema::dropIfExists('users');
    }
}
