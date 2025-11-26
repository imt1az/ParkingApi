<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable()->unique();
            $t->string('phone')->unique();
            $t->enum('role', ['driver','provider','admin'])->default('driver');
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
            $t->charset = 'utf8mb4';
            $t->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
