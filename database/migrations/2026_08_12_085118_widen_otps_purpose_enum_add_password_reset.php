<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->enum('purpose', ['phone_verification', 'role_activation', 'password_reset'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->enum('purpose', ['phone_verification', 'role_activation'])->change();
        });
    }
};
