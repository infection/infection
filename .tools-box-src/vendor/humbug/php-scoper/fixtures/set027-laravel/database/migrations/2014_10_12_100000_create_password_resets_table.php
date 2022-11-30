<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Schema;
use _HumbugBoxb47773b41c19\Illuminate\Database\Schema\Blueprint;
use _HumbugBoxb47773b41c19\Illuminate\Database\Migrations\Migration;
class CreatePasswordResetsTable extends Migration
{
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
\class_alias('_HumbugBoxb47773b41c19\\CreatePasswordResetsTable', 'CreatePasswordResetsTable', \false);
