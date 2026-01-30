<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Documentación UAFE requerida</title>
</head>
<body>
    <p>Estimado/a {{ $proveedor->nombre }},</p>
    <p>Para completar el proceso de registro, necesitamos los siguientes documentos UAFE:</p>
    <ul>
        @foreach ($documentos as $documento)
            <li>{{ $documento->titulo }}</li>
        @endforeach
    </ul>
    <p>Gracias por su colaboración.</p>
</body>
</html>
