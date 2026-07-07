<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Habilitar extensiones PostGIS en Supabase (ya vienen habilitadas,
        // pero esto es idempotente y documenta la dependencia)
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis_topology');
        DB::statement('CREATE EXTENSION IF NOT EXISTS fuzzystrmatch');
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis_tiger_geocoder');
    }

    public function down(): void
    {
        // No eliminamos las extensiones en down() para no afectar otros proyectos
    }
};
