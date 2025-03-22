He implementado la restricción para que la columna de usuario solo sea visible para los administradores en ambos recursos:

1. **En RecordingResource**:
   - La columna `user.name` ahora solo es visible para el usuario con ID 1 (administrador)
   - Utilicé `Filament::auth()->user()->id === 1` como condición para determinar si se muestra la columna
2. **En TranscriptionResource**:
   - Añadí una nueva columna `recording.user.name` que muestra el usuario propietario de la grabación
   - Esta columna también está configurada para ser visible solo para administradores
   - Utilicé la misma condición `Filament::auth()->user()->id === 1` para mantener la consistencia

Con estos cambios, los usuarios regulares no podrán ver información de otros usuarios en las tablas de grabaciones y transcripciones, mientras que los administradores tendrán acceso a esta información para gestionar el sistema.

Esto complementa la restricción de acceso que implementamos anteriormente, donde los usuarios solo pueden ver sus propias grabaciones y transcripciones, cumpliendo así con el requisito de que "los usuarios NO pueden ni deben ver las grabaciones y transcripciones de los otros usuarios".