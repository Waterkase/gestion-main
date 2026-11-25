<div>
    <!-- Estilos de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">Visor Territorial - Comunas y Circuitos</h2>
        <button
            type="button"
            wire:click="cargarPoligonos"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
            Actualizar Capa
        </button>
    </div>

    <!-- Contenedor protegido con wire:ignore -->
    <div wire:ignore>
        <div id="mapa-gis" style="height: 600px; width: 100%; border-radius: 8px; z-index: 1;"></div>
    </div>

    <!-- Script de Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            const map = L.map('mapa-gis').setView([10.4806, -66.9036], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let capaPoligonos = null;

            function estiloPoligono(feature) {
                return {
                    fillColor: '#2563eb',
                    weight: 2,
                    opacity: 1,
                    color: '#1e3a8a',
                    dashArray: '3',
                    fillOpacity: 0.35
                };
            }

            function enCadaPoligono(feature, layer) {
                if (feature.properties && feature.properties.ComuCircu) {
                    layer.bindPopup(`
                        <div class="p-1">
                            <span class="text-xs text-gray-500 uppercase font-semibold">Comuna / Circuito</span>
                            <p class="text-sm font-bold text-gray-900">${feature.properties.ComuCircu}</p>
                        </div>
                    `);
                }

                layer.on({
                    mouseover: function (e) {
                        const target = e.target;
                        target.setStyle({
                            weight: 3,
                            color: '#f59e0b',
                            fillOpacity: 0.6
                        });
                        target.bringToFront();
                    },
                    mouseout: function (e) {
                        if (capaPoligonos) {
                            capaPoligonos.resetStyle(e.target);
                        }
                    }
                });
            }

            function renderizarCapa(geoJsonData) {
                if (capaPoligonos) {
                    map.removeLayer(capaPoligonos);
                }

                capaPoligonos = L.geoJSON(geoJsonData, {
                    style: estiloPoligono,
                    onEachFeature: enCadaPoligono
                }).addTo(map);

                if (geoJsonData && geoJsonData.features && geoJsonData.features.length > 0) {
                    map.fitBounds(capaPoligonos.getBounds(), { padding: [20, 20] });
                }
            }

            // Carga inicial
            renderizarCapa(@json(json_decode($geoJsonPoligonos)));

            // Actualización dinámica cuando se dispara 'poligonos-actualizados' desde Livewire
            Livewire.on('poligonos-actualizados', ({ geojson }) => {
                renderizarCapa(geojson);
            });
        });
    </script>
</div>