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
            $table->text('username');
            $table->text('email');
            $table->text('password')->nullable();
            $table->jsonb('profile')->nullable();
            $table->text('provider')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->primary('username');
        });

        Schema::create('remote_auth_memberships', function (Blueprint $table) {
            $table->text('username');
            $table->text('group');
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
