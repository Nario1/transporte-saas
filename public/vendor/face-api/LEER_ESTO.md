# Instrucciones — Descargar Modelos face-api.js

Los modelos de reconocimiento facial deben colocarse en esta carpeta:
`public/vendor/face-api/models/`

## Opción 1: Descarga Manual (Recomendada)

1. Ir a: https://github.com/vladmandic/face-api/tree/master/model
2. Descargar los siguientes archivos de modelos:
   - `tiny_face_detector_model-weights_manifest.json`
   - `tiny_face_detector_model-shard1`
   - `face_landmark_68_model-weights_manifest.json`
   - `face_landmark_68_model-shard1`
   - `face_recognition_model-weights_manifest.json`
   - `face_recognition_model-shard1`
   - `face_recognition_model-shard2`

3. Copiar todos a: `c:\xampp\htdocs\transporte-saas\public\vendor\face-api\models\`

## Opción 2: CDN (Solo desarrollo/pruebas)

Los modelos también se cargan desde CDN automáticamente en el código:
```javascript
const MODELS_URL = '/vendor/face-api/models'; // Si hay modelos locales
// Alternativa CDN: 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model'
```

Para usar CDN en lugar de modelos locales, cambiar en las vistas:
```javascript
const MODELS_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
```

## Opción 3: Script PowerShell de Descarga

```powershell
$base = "https://raw.githubusercontent.com/vladmandic/face-api/master/model"
$dest = "c:\xampp\htdocs\transporte-saas\public\vendor\face-api\models"
New-Item -ItemType Directory -Force -Path $dest

$files = @(
    "tiny_face_detector_model-weights_manifest.json",
    "tiny_face_detector_model-shard1",
    "face_landmark_68_model-weights_manifest.json",
    "face_landmark_68_model-shard1",
    "face_recognition_model-weights_manifest.json",
    "face_recognition_model-shard1",
    "face_recognition_model-shard2"
)

foreach ($f in $files) {
    Invoke-WebRequest -Uri "$base/$f" -OutFile "$dest\$f"
    Write-Host "Descargado: $f"
}
Write-Host "Modelos descargados exitosamente."
```

## Nota de Seguridad

Los embeddings faciales (descriptores numéricos de 128 dimensiones) se almacenan en
la base de datos como JSON. **NO se almacenan imágenes biométricas en la BD** — solo
el descriptor matemático. Cumple con principios de privacidad por diseño.
