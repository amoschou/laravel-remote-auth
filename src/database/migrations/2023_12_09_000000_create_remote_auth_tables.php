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
        Schema::create('remote_auth_users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('id')->nullable();
            $table->string('display_name')->nullable();
            $table->string('email')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('remote_auth_memberships', function (Blueprint $table) {
            $table->string('username');
            $table->string('group');
            $table->foreign('username')->references('username')->on('remote_auth_users');
            $table->primary(['username', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_auth_memberships');
        Schema::dropIfExists('remote_auth_users');
    }
};
