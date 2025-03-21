# Proyecto de Grabación y Transcripción de Audio con Laravel + Filament

## Descripción General

Esta aplicación permite a los usuarios grabar reuniones en audio desde sus dispositivos móviles, almacenarlas en sus cuentas personales, transcribirlas automáticamente usando servicios de IA, y recibir estas transcripciones por email en formato Markdown. La plataforma está construida con Laravel como backend y Filament como framework de administración, proporcionando una interfaz intuitiva tanto para usuarios como para administradores.

## Modelos del Sistema

### 1. User (Usuario)
**Funcionalidad**: Gestiona la autenticación y datos de usuarios.
**Campos principales**: 
- Datos personales (nombre, email, contraseña)
- Configuraciones de cuenta
- Relaciones con grabaciones

**Lógica**:
- Maneja procesos de registro y autenticación
- Gestiona permisos y roles (usando Spatie Permission o similar)
- Almacena preferencias de notificación

### 2. Recording (Grabación)
**Funcionalidad**: Almacena y gestiona las grabaciones de audio.
**Campos principales**:
- user_id: Relación con el usuario propietario
- title: Título de la grabación
- description: Descripción opcional
- file_path: Ruta al archivo de audio
- file_name: Nombre del archivo
- mime_type: Tipo MIME del archivo
- file_size: Tamaño en bytes
- duration: Duración en segundos
- status: Estado actual (pending, processing, completed, failed)
- metadata: Datos adicionales en formato JSON
- is_public: Indica si la grabación es accesible públicamente
- processed_at: Cuándo se procesó completamente

**Lógica**:
- Gestiona el ciclo de vida completo de una grabación
- Controla los procesos de subida y almacenamiento
- Proporciona métodos para acceder a metadatos y estado
- Implementa políticas de acceso y privacidad

### 3. Transcription (Transcripción)
**Funcionalidad**: Almacena y gestiona las transcripciones de las grabaciones.
**Campos principales**:
- recording_id: Relación con la grabación
- content: Contenido de la transcripción en Markdown
- language: Idioma de la transcripción
- service_used: Servicio de IA utilizado
- service_response: Respuesta completa del servicio de IA
- confidence_score: Puntuación de confianza de la transcripción
- is_edited: Indica si ha sido editada manualmente
- email_sent: Indica si se ha enviado el email
- email_sent_at: Cuándo se envió el email

**Lógica**:
- Vincula transcripciones con grabaciones
- Gestiona el formato y presentación del texto
- Facilita búsquedas y filtrado de contenido
- Controla el proceso de notificación por email

### 4. Tag (Etiqueta) - Opcional
**Funcionalidad**: Permite categorizar grabaciones.
**Campos principales**:
- name: Nombre de la etiqueta

**Lógica**:
- Facilita organización y búsqueda de grabaciones
- Permite agrupar grabaciones por temas o proyectos

### 5. RecordingShare (Compartir Grabación) - Opcional
**Funcionalidad**: Gestiona el acceso compartido a grabaciones.
**Campos principales**:
- recording_id: Relación con la grabación
- user_id: Usuario con quien se comparte
- permission_level: Nivel de permiso otorgado
- token: Token único para acceso por enlace
- expires_at: Fecha de expiración

**Lógica**:
- Controla los permisos de acceso compartido
- Gestiona enlaces temporales
- Implementa límites de tiempo y permisos

## Flujo de Funcionamiento

1. **Grabación de Audio**:
   - El usuario inicia sesión y accede a la interfaz de grabación
   - La aplicación solicita permisos de micrófono
   - El audio se graba en tiempo real y se almacena temporalmente
   - Al finalizar, el archivo se sube al servidor y se crea un registro en la tabla `recordings`
   - El estado inicial es "pending"

2. **Procesamiento de Audio**:
   - Un job en segundo plano toma las grabaciones pendientes
   - Procesa el archivo (normalización, compresión si es necesario)
   - Actualiza metadatos como duración y tamaño
   - Cambia el estado a "processing"

3. **Transcripción**:
   - El archivo procesado se envía a un servicio de IA (OpenAI, Google, etc.)
   - La respuesta del servicio se recibe y se formatea a Markdown
   - Se crea un registro en la tabla `transcriptions`
   - Se vincula con la grabación correspondiente
   - El estado de la grabación cambia a "completed"

4. **Notificación**:
   - Se genera un email con la transcripción formateada usando plantillas Blade
   - Se envía al usuario mediante Resend API a través del sistema de emails de Laravel
   - El envío se procesa de forma asíncrona mediante colas
   - Se registra el envío en la tabla `transcriptions` con fecha y hora

5. **Gestión y Acceso**:
   - El usuario puede acceder a sus grabaciones y transcripciones
   - Puede buscar, filtrar y organizar su biblioteca
   - Opciones para editar transcripciones manualmente
   - Puede compartir grabaciones con otros usuarios si el módulo está habilitado

## Características Técnicas

1. **Sistema de Colas**:
   - Utiliza Laravel Queues para procesar tareas asíncronas
   - Implementa jobs para transcripción y envío de emails
   - Configuración de reintentos, backoff y manejo de errores
   - Monitoreo de estado de los jobs

2. **Almacenamiento**:
   - Configuración de discos específicos para audio y transcripciones
   - Políticas de retención y limpieza

3. **Seguridad**:
   - Autenticación robusta con Laravel Fortify o similar
   - Autorización basada en políticas
   - Protección contra ataques comunes

4. **Panel de Administración**:
   - Interface completa con Filament para administradores
   - Gestión de usuarios, grabaciones y transcripciones
   - Estadísticas y reportes

5. **API**:
   - Endpoints para integración con otras aplicaciones
   - Autenticación mediante tokens o OAuth

Este diseño proporciona una base sólida para construir una aplicación completa de grabación y transcripción de audio, con un enfoque modular que permite expandir funcionalidades según sea necesario.