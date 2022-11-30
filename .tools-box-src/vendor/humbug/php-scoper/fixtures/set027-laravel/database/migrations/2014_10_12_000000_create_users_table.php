<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Schema;
use _HumbugBoxb47773b41c19\Illuminate\Database\Schema\Blueprint;
use _HumbugBoxb47773b41c19\Illuminate\Database\Migrations\Migration;
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
\class_alias('_HumbugBoxb47773b41c19\\CreateUsersTable', 'CreateUsersTable', \false);
