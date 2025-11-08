<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_spaces', function (Blueprint $t) {
            $t->id();

            $t->foreignId('provider_id')
              ->constrained('users')
              ->cascadeOnDelete();

            $t->string('title');
            $t->text('description')->nullable();
            $t->string('address')->nullable();

            $t->decimal('lat', 9, 6);
            $t->decimal('lng', 9, 6);

            $t->unsignedInteger('capacity')->default(1);
            $t->decimal('height_limit', 5, 2)->nullable();
            $t->boolean('is_active')->default(true);

            $t->timestamps();
        });

        // 1) POINT কলাম (SRID attribute ছাড়াই)
        DB::statement("ALTER TABLE parking_spaces ADD COLUMN location POINT NOT NULL");

        // 2) Spatial index
        DB::statement("CREATE SPATIAL INDEX idx_parking_spaces_location ON parking_spaces (location)");

        // 3) BEFORE INSERT trigger: lat/lng থেকে SRID 4326 সহ location সেট
        DB::statement("
            CREATE TRIGGER trg_parking_spaces_bi
            BEFORE INSERT ON parking_spaces
            FOR EACH ROW
            BEGIN
              SET NEW.location = ST_GeomFromText(
                CONCAT('POINT(', NEW.lng, ' ', NEW.lat, ')'), 4326
              );
            END
        ");

        // 4) BEFORE UPDATE trigger: lat/lng বদলালে location আপডেট
        DB::statement("
            CREATE TRIGGER trg_parking_spaces_bu
            BEFORE UPDATE ON parking_spaces
            FOR EACH ROW
            BEGIN
              IF (NEW.lat <> OLD.lat) OR (NEW.lng <> OLD.lng) THEN
                SET NEW.location = ST_GeomFromText(
                  CONCAT('POINT(', NEW.lng, ' ', NEW.lat, ')'), 4326
                );
              END IF;
            END
        ");
    }

    public function down(): void
    {
        // ট্রিগার থাকলে ড্রপ করো (IF EXISTS কিছু MySQL-এ নেই, তাই নিরাপদভাবে চেষ্টা)
        try { DB::statement("DROP TRIGGER IF EXISTS trg_parking_spaces_bi"); } catch (\Throwable $e) {}
        try { DB::statement("DROP TRIGGER IF EXISTS trg_parking_spaces_bu"); } catch (\Throwable $e) {}

        // স্পেশাল ইনডেক্স ড্রপ
        try { DB::statement("DROP INDEX idx_parking_spaces_location ON parking_spaces"); } catch (\Throwable $e) {}

        Schema::dropIfExists('parking_spaces');
    }
};
