@extends('layouts.admin')
@section('title', 'Kiosco - Paradero: ' . $paradero->nombre)

@section('content')
<div class="row mb-4">
    <div class="col-12 text-center">
        <h2><i class="fa-solid fa-location-dot"></i> Paradero: {{ $paradero->nombre }} ({{ strtoupper($paradero->tipo) }})</h2>
        <p class="text-muted">Ruta: {{ $paradero->ruta->nombre }}</p>
    </div>
</div>

<div class="row justify-content-center">
    <!-- Camera Area -->
    <div class="col-md-8 text-center">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4" style="background:#f8f9fa; border-radius: 8px;">
                <h4 class="mb-3"><i class="fa-solid fa-camera"></i> Control de Asistencia Facial</h4>
                <div id="video-container" style="position: relative; display: inline-block; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                    <video id="videoElement" width="640" height="480" autoplay muted playsinline style="background:#000; border-radius:12px;"></video>
                    <!-- Face-API Overlay Canvas -->
                    <canvas id="overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;"></canvas>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" id="btn-iniciar" style="padding:10px 24px; font-size:16px;">
                        <i class="fa-solid fa-play"></i> Iniciar Escáner
                    </button>
                    <button class="btn btn-secondary" id="btn-detener" disabled style="padding:10px 24px; font-size:16px;">
                        <i class="fa-solid fa-pause"></i> Pausar Escáner
                    </button>
                </div>

                <div id="status-box" class="alert alert-info mt-3" style="display:none;">
                    Cargando modelos e inicializando sistema...
                </div>
            </div>
        </div>
    </div>

    <!-- Match Results Area -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0" style="height: 100%;">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="fa-solid fa-list-check"></i> Registros Recientes</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="log-list" style="max-height: 500px; overflow-y: auto;">
                    <li class="list-group-item text-muted text-center py-4 fs-6">
                        Esperando rostros...
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@push('scripts')
<!-- Cargar Face API -->
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const MODELS_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js/weights/';
    const API_ROSTROS_URL = '{{ route("paraderos.api.rostros") }}';
    const API_CHECKIN_URL = '{{ route("paraderos.checkin", $paradero->id) }}';

    const video = document.getElementById('videoElement');
    const overlay = document.getElementById('overlay');
    const btnIniciar = document.getElementById('btn-iniciar');
    const btnDetener = document.getElementById('btn-detener');
    const statusBox = document.getElementById('status-box');
    const logList = document.getElementById('log-list');

    let isScanning = false;
    let scanInterval = null;
    let labeledFaceDescriptors = [];
    let faceMatcher = null;
    let lastMatchTime = 0; // Prevenir múltiples checkins rápidos del mismo usuario

    // Mostrar mensaje de estado
    function setStatus(msg, type = 'info') {
        statusBox.style.display = 'block';
        statusBox.className = `alert alert-${type} mt-3 mb-0 text-start fw-bold`;
        statusBox.innerHTML = msg;
    }

    // Agregar registro visual al log
    function addLogRecord(name, distance, status) {
        if (logList.querySelector('.text-muted')) {
            logList.innerHTML = ''; // Quitar mensaje de espera
        }

        const li = document.createElement('li');
        li.className = 'list-group-item';
        
        let badgeColor = status === 'match' ? 'bg-success' : 'bg-danger';
        let statusText = status === 'match' ? 'Reconocido' : 'Desconocido';

        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong>${name}</strong>
                <span class="badge ${badgeColor}">${statusText}</span>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size:0.85rem;">
                <span>Distancia: ${distance.toFixed(3)}</span>
                <span>${new Date().toLocaleTimeString()}</span>
            </div>
        `;

        logList.prepend(li);
    }

    // 1. Cargar la base de datos de conductores y crear el FaceMatcher
    async function initDatabase() {
        setStatus(' Descargando base de datos de rostros...', 'info');
        try {
            const resp = await fetch(API_ROSTROS_URL);
            const data = await resp.json();

            if (!data || data.length === 0) {
                setStatus('<i class="fa-solid fa-circle-exclamation"></i> No se encontraron conductores con rostros registrados.', 'warning');
                return false;
            }

            labeledFaceDescriptors = data.map(record => {
                const embedArray = JSON.parse(record.embedding);
                const float32Arr = new Float32Array(embedArray);
                return new faceapi.LabeledFaceDescriptors(
                    record.conductor.id.toString() + '|' + record.conductor.nombre,
                    [float32Arr]
                );
            });

            // Umbral de 0.55 para mayor estrictez
            faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.55);
            setStatus(`<i class="fa-solid fa-circle-check"></i> Base de datos cargada. ${labeledFaceDescriptors.length} rostros listos.`, 'success');
            return true;

        } catch (e) {
            console.error(e);
            setStatus('<i class="fa-solid fa-circle-xmark"></i> Error al cargar conductores.', 'danger');
            return false;
        }
    }

    // 2. Iniciar la Cámara y los Modelos
    btnIniciar.addEventListener('click', async () => {
        btnIniciar.disabled = true;
        
        // Cargar Base Datos
        let ok = await initDatabase();
        if (!ok) {
            btnIniciar.disabled = false;
            return;
        }

        setStatus('Cargando modelos de IA...', 'info');
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL)
            ]);

            setStatus('Encendiendo cámara...', 'info');
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 640, height: 480 } 
            });
            video.srcObject = stream;

            video.onloadeddata = () => {
                // Ajustar el canvas overlay
                const displaySize = { width: video.width, height: video.height };
                faceapi.matchDimensions(overlay, displaySize);

                setStatus('<i class="fa-solid fa-circle-check"></i> Kiosco Operativo. Buscando rostros...', 'success');
                btnDetener.disabled = false;
                startScanning(displaySize);
            };

        } catch (error) {
            setStatus('<i class="fa-solid fa-circle-xmark"></i> Error: ' + error.message, 'danger');
            btnIniciar.disabled = false;
        }
    });

    // 3. Detener la cámara
    btnDetener.addEventListener('click', () => {
        isScanning = false;
        if (scanInterval) clearInterval(scanInterval);
        
        const stream = video.srcObject;
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
        }
        video.srcObject = null;
        
        const ctx = overlay.getContext('2d');
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        btnIniciar.disabled = false;
        btnDetener.disabled = true;
        setStatus('<i class="fa-solid fa-circle-pause"></i> Escáner pausado.', 'warning');
    });

    // 4. Bucle principal de escaneo
    function startScanning(displaySize) {
        isScanning = true;
        
        scanInterval = setInterval(async () => {
            if (!isScanning) return;

            // Detectar todos los rostros en el frame actual
            const detections = await faceapi.detectAllFaces(video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);

            if (detections.length === 0) return;

            // Dibujar las cajas y comparar
            resizedDetections.forEach(detection => {
                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                const box = detection.detection.box;

                let drawColor = bestMatch.label === 'unknown' ? 'red' : 'green';
                const drawBox = new faceapi.draw.DrawBox(box, { 
                    label: bestMatch.label === 'unknown' ? 'Desconocido' : bestMatch.label.split('|')[1], 
                    boxColor: drawColor 
                });
                drawBox.draw(overlay);

                // Si es un match válido procesarlo
                if (bestMatch.label !== 'unknown') {
                    processMatch(bestMatch);
                }
            });

        }, 500); // 2 fps para no saturar la CPU
    }

    // 5. Enviar comprobación al servidor
    async function processMatch(bestMatch) {
        const parts = bestMatch.label.split('|');
        const conductorId = parts[0];
        const name = parts[1];
        const distance = bestMatch.distance;

        const now = Date.now();
        // Cooldown de 10 segundos por rostro para evitar spam al servidor
        if (now - lastMatchTime < 10000) return;
        lastMatchTime = now;

        addLogRecord(name, distance, 'match');

        try {
            const resp = await fetch(API_CHECKIN_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    conductor_id: conductorId,
                    distancia: distance
                })
            });

            const result = await resp.json();
            
            if (result.status === 'success') {
                setStatus(`<i class="fa-solid fa-circle-check"></i> ${result.mensaje}`, 'success');
            } else {
                setStatus(`<i class="fa-solid fa-circle-exclamation"></i> ${result.message || 'Ocurrió un error'}`, 'warning');
            }

        } catch (e) {
            console.error(e);
            setStatus('<i class="fa-solid fa-circle-xmark"></i> Falló la comunicación con el servidor.', 'danger');
        }
    }
</script>
@endpush
