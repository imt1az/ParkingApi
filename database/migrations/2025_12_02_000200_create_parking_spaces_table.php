<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spaces', function (Blueprint $t) {
            $t->id();
            $t->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('address')->nullable();
            $t->string('place_label')->nullable();
            $t->decimal('lat', 9, 6);
            $t->decimal('lng', 9, 6);
            $t->unsignedInteger('capacity')->default(1);
            $t->decimal('height_limit', 5, 2)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->charset = 'utf8mb4';
            $t->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_spaces');
    }
};
