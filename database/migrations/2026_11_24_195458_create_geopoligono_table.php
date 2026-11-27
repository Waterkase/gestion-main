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
        Schema::create('geopoligono', function (Blueprint $table) {
            $table->id();
            $table->string('comucircu');
            $table->string('parroquia');
            // Usamos geometry (o multiPolygon/polygon) con SRID 4326 para compatibilidad total con Leaflet y QGIS
            $table->magellanGeometry('geom', 4326);
            $table->timestamps();

            // Índice espacial para acelerar consultas geográficas
            $table->spatialIndex('geom');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geopoligono');
    }
};
