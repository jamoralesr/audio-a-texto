<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acta Lista</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        h2 {
            color: #3498db;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .recording-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        .recording-info p {
            margin: 5px 0;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #eee;
            margin-bottom: 25px;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .button:hover {
            background-color: #2980b9;
        }
        pre {
            white-space: pre-wrap;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tu Acta Está Lista</h1>
        <p>Hemos procesado tu grabación y generado un acta formal.</p>
    </div>

    <div class="recording-info">
        <h2>Detalles de la Grabación</h2>
        <p><strong>Título:</strong> {{ $recording->title }}</p>
        <p><strong>Fecha de grabación:</strong> {{ $recording->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Duración:</strong> {{ gmdate('H:i:s', $recording->duration) }}</p>
    </div>

    <div class="content">
        <h2>Acta de la Reunión</h2>
        <div>
            {!! Str::markdown($record->content) !!}
        </div>
    </div>

    <div class="footer">
        <p>Este email fue enviado automáticamente. Por favor no responda a este correo.</p>
        <p>&copy; {{ date('Y') }} Audio a Texto. Todos los derechos reservados.</p>
    </div>
</body>
</html>
