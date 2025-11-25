<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old triggers (if they exist)
        try { DB::statement("DROP TRIGGER IF EXISTS trg_parking_spaces_bi"); } catch (\Throwable $e) {}
        try { DB::statement("DROP TRIGGER IF EXISTS trg_parking_spaces_bu"); } catch (\Throwable $e) {}

        // Recreate triggers with correct POINT order: POINT(lng lat)
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
        try { DB::statement("DROP TRIGGER IF EXISTS trg_parking_spaces_bi"); } catch (\Throwable $e) {}
        try { DB::statement("DROP TRIGGER IF EXISTS trg_parking_spaces_bu"); } catch (\Throwable $e) {}
    }
};
