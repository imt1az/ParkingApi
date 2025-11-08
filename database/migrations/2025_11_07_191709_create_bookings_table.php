<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('bookings', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $t->foreignId('space_id')->constrained('parking_spaces')->cascadeOnDelete();
        $t->dateTime('start_ts');
        $t->dateTime('end_ts');
        $t->decimal('hours', 6, 2)->default(0);
        $t->decimal('price_total', 10, 2)->default(0);
        $t->enum('status', ['reserved','confirmed','checked_in','checked_out','completed','cancelled'])->default('reserved');
        $t->dateTime('hold_expires_at')->nullable();
        $t->dateTime('checked_in_at')->nullable();
        $t->dateTime('checked_out_at')->nullable();
        $t->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('bookings');
}
};
