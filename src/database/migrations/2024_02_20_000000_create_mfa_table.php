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
        // Schema::create('remote_auth_mfa_codes', function (Blueprint $table) {
        //     $table->text('username');
        //     $table->text('request_id');
        //     $table->text('csrf');
        //     $table->text('code');
        //     $table->dateTimeTz('expiry');
        //     $table->primary('username');
        //     $table->foreign('username')->references('username')->on('remote_auth_users');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('remote_auth_mfa_codes');
    }
};
