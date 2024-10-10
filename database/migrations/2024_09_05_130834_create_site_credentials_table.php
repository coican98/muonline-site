<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteCredentialsTable extends Migration
{
    public function up()
    {
        Schema::create('siteCredentials', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Use id as primary key
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siteCredentials');
    }
}
