<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('name');
            $table->string('nationalId')->unique();
            $table->string('code')->unique();
            $table->string('phoneNumber')->unique();
            $table->string('email')->unique();
            $table->date('dob');
            $table->string('status')->default('ACTIVE');
            $table->string('position');
            $table->string('password');
            $table->date('createDate');
            $table->timestamp('email_verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
