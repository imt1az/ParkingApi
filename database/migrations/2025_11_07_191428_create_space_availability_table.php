<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_availability', function (Blueprint $t) {
            $t->id();
            $t->foreignId('space_id')->constrained('parking_spaces')->cascadeOnDelete();
            $t->dateTime('start_ts');
            $t->dateTime('end_ts');
            $t->decimal('base_price_per_hour', 10, 2);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_availability');
    }
};
