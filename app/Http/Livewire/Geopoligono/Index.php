<?php

namespace App\Http\Livewire\Geopoligono;

use App\Models\geopoligono;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Index extends Component
{

public string $geoJsonPoligonos = '';
public int $totalPoligonos = 0;

    public function mount(): void
    {
        $this->cargarPoligonos();
    }

    public function cargarPoligonos(): void
    {
        $this->totalPoligonos = geopoligono::whereNotNull('geom')->count();
        // PostGIS construye directamente el FeatureCollection válido en GeoJSON
        $resultado = DB::selectOne("
            SELECT jsonb_build_object(
                'type', 'FeatureCollection',
                'features', COALESCE(jsonb_agg(feature), '[]'::jsonb)
            ) AS geojson
            FROM (
                SELECT jsonb_build_object(
                    'type', 'Feature',
                    'id', id,
                    'geometry', ST_AsGeoJSON(geom)::jsonb,
                    'properties', jsonb_build_object(
                        'id', id,
                        'comucircu', \"comucircu\"
                    )
                ) AS feature
                FROM geopoligono
                WHERE geom IS NOT NULL
            ) features;
        ");

        $this->geoJsonPoligonos = $resultado->geojson ?? '{"type":"FeatureCollection","features":[]}';
        
        $this->dispatch('poligonos-actualizados', geojson: json_decode($this->geoJsonPoligonos));
    }

    public function render()

    {
        return view('livewire.geopoligono.index');
    }
}
