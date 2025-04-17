<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->boolean('iranian')->default(true);
            $table->string('n_code', 15);
            $table->boolean('gender')->nullable();
            $table->string('f_name_fa', 40)->nullable();
            $table->string('l_name_fa', 50)->nullable();
            $table->string('f_name_en', 40)->nullable();
            $table->string('l_name_en', 50)->nullable();
            $table->string('father', 40)->nullable();
            $table->string('sh_sh', 10)->nullable();
            $table->string('born_place', 30)->nullable();
            $table->string('born_date', 10)->nullable();
            $table->string('din', 20)->nullable();
            $table->string('mazhab', 20)->nullable();
            $table->tinyInteger('nezam_id')->nullable()->unsigned();
            $table->tinyInteger('taahol')->nullable()->unsigned();
            $table->tinyInteger('child_qty')->default(0)->unsigned();
            $table->tinyInteger('maghta_id')->nullable()->unsigned();
            $table->string('reshte', 30)->nullable();
            $table->string('job', 35)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('image_url', 30)->nullable();
            $table->boolean('user_confirmed')->default(0);
            $table->char('user_confirmed_time', 19)->nullable();
            $table->bigInteger('approved_by_user_role_id')->default(0);
            $table->char('approved_time', 19)->nullable();
            $table->char('created', 19);
            $table->char('updated', 19)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
