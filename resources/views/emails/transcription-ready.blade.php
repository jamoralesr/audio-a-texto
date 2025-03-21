<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transcripción Lista - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        h1 {
            color: #2563eb;
            margin-top: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .recording-info {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .recording-info h3 {
            margin-top: 0;
            color: #1e40af;
        }
        .transcription {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            white-space: pre-wrap;
        }
        .transcription-preview {
            max-height: 300px;
            overflow: hidden;
            position: relative;
        }
        .transcription-preview::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .button.secondary {
            background-color: #64748b;
        }
        .button.secondary:hover {
            background-color: #475569;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #64748b;
            text-align: center;
        }
        .metadata {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 5px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .button {
                display: block;
                margin-right: 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>Tu transcripción está lista</p>
    </div>
    
    <div class="content">
        <p>Hola {{ $recording->user->name ?? 'Usuario' }},</p>
        
        <p>La transcripción de tu grabación <strong>"{{ $recording->title }}"</strong> está lista para revisar.</p>
        
        <div class="recording-info">
            <h3>Detalles de la grabación</h3>
            <p class="metadata"><strong>Duración:</strong> {{ gmdate('H:i:s', $recording->duration) }}</p>
            <p class="metadata"><strong>Fecha de grabación:</strong> {{ $recording->created_at->format('d/m/Y H:i') }}</p>
            <p class="metadata"><strong>Idioma detectado:</strong> {{ $transcription->language }}</p>
            <p class="metadata"><strong>Servicio utilizado:</strong> {{ $transcription->service_used }}</p>
        </div>
        
        <h3>Vista previa de la transcripción</h3>
        <div class="transcription transcription-preview">
            {!! nl2br(e(Str::limit($transcription->content, 500))) !!}
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            @php
                $transcriptionUrl = config('app.url') . '/admin/transcriptions/' . $transcription->id;
                $recordingsUrl = config('app.url') . '/admin/recordings';
            @endphp
            <a href="{{ $transcriptionUrl }}" class="button">Ver transcripción completa</a>
            <a href="{{ $recordingsUrl }}" class="button secondary">Ver todas mis grabaciones</a>
        </div>
        
        <p>Puedes acceder a esta transcripción en cualquier momento desde tu panel de control.</p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        <p>Este email fue enviado automáticamente, por favor no responda a este mensaje.</p>
    </div>
</body>
</html>
