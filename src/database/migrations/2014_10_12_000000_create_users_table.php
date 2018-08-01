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
            $table->increments('id')->unsigned();
            $table->char('first_name',90);
            $table->char('last_name',90);
            $table->char('email',100)->unique();
            $table->char('mobile_number',20)->unique();
            $table->string('image_name');
            $table->char('country_code',10);
            $table->boolean('verified')->default(0);
            $table->date('birth_date');
            $table->boolean('block')->default(0);
            $table->softDeletes('deleted_at');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->rememberToken();
            $table->engine = 'InnoDB';
    
    
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
