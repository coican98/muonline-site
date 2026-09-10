<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteCredentialsTable extends Migration
{
    public function up()
    {
        Schema::create('siteCredentials', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->boolean('global_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siteCredentials');
    }
}
