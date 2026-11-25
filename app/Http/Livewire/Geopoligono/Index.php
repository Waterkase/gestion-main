<?php

namespace App\Http\Livewire\Geopoligono;

use Livewire\Component;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\geopoligono;

class Index extends Component
{

public string $geoJsonPoligonos = '';

    public function mount(): void
    {
        $this->cargarPoligonos();
    }

    public function cargarPoligonos(): void
    {
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
    }

    public function render()

    {
        return view('livewire.geopoligono.index');
    }
}
