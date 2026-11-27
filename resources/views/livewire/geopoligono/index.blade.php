<div>
    <!-- Estilos de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Visor Territorial - Comunas y Circuitos</h2>
            <p class="text-sm text-gray-500">Polígonos cargados: <span class="font-semibold">{{ $totalPoligonos }}</span></p>
        </div>
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

    <!-- Ventana emergente (modal) al seleccionar un polígono -->
    <div
        id="modal-poligono"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-poligono-titulo"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between border-b pb-3">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                        Registro de Tabla: geopoligono
                    </span>
                    <h3 id="modal-poligono-titulo" class="text-lg font-bold text-gray-900">
                        Detalle del Geopolígono
                    </h3>
                </div>
                <button
                    type="button"
                    id="cerrar-modal-poligono"
                    class="rounded px-2 py-1 text-sm font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                >
                    ✕
                </button>
            </div>

            <div class="mt-4 space-y-2.5 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <span class="font-medium text-gray-500">ID:</span>
                    <span id="detalle-id" class="font-mono font-semibold text-gray-900">-</span>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <span class="font-medium text-gray-500">Comuna:</span>
                    <span id="detalle-comucircu" class="text-right font-semibold text-gray-900">-</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    type="button"
                    id="btn-aceptar-modal"
                    class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Cerrar
                </button>
            </div>
        </div>
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
            let poligonoSeleccionado = null;

            const modal = document.getElementById('modal-poligono');
            const detalleId = document.getElementById('detalle-id');
            const detallecomucircu = document.getElementById('detalle-comucircu');


            function abrirVentanaPoligono(props) {
                detalleId.textContent = props.id ?? 'N/A';
                detallecomucircu.textContent = props.comucircu ?? 'Sin nombre';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function cerrarVentanaPoligono() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');

                if (poligonoSeleccionado && capaPoligonos) {
                    capaPoligonos.resetStyle(poligonoSeleccionado);
                    poligonoSeleccionado = null;
                }
            }

            document.getElementById('cerrar-modal-poligono').addEventListener('click', cerrarVentanaPoligono);
            document.getElementById('btn-aceptar-modal').addEventListener('click', cerrarVentanaPoligono);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cerrarVentanaPoligono();
                }
            });

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

            function estiloSeleccionado() {
                return {
                    fillColor: '#f59e0b',
                    weight: 3,
                    opacity: 1,
                    color: '#b45309',
                    dashArray: '',
                    fillOpacity: 0.55
                };
            }

            function enCadaPoligono(feature, layer) {
                const props = feature.properties || {};
                const nombre = props.comucircu || 'Sin nombre';

                // Ventana flotante rápida al posicionarse sobre el polígono
                layer.bindTooltip(
                    `<div><strong>${nombre}</strong><br><span style="font-size:11px;">Clic para ver detalles</span></div>`,
                    { sticky: true, direction: 'top' }
                );

                layer.on({
                    mouseover: function (e) {
                        const target = e.target;
                        if (target !== poligonoSeleccionado) {
                            target.setStyle({
                                weight: 3,
                                color: '#f59e0b',
                                fillOpacity: 0.6
                            });
                        }
                        target.bringToFront();
                    },
                    mouseout: function (e) {
                        const target = e.target;
                        if (capaPoligonos && target !== poligonoSeleccionado) {
                            capaPoligonos.resetStyle(target);
                        }
                    },
                    click: function (e) {
                        L.DomEvent.stopPropagation(e);

                        if (poligonoSeleccionado && capaPoligonos && poligonoSeleccionado !== e.target) {
                            capaPoligonos.resetStyle(poligonoSeleccionado);
                        }

                        poligonoSeleccionado = e.target;
                        poligonoSeleccionado.setStyle(estiloSeleccionado());
                        poligonoSeleccionado.bringToFront();

                        abrirVentanaPoligono(props);
                    }
                });
            }

            function renderizarCapa(geoJsonData) {
                cerrarVentanaPoligono();

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

            renderizarCapa(@json(json_decode($geoJsonPoligonos)));

            Livewire.on('poligonos-actualizados', ({ geojson }) => {
                renderizarCapa(geojson);
            });
        });
    </script>
</div>