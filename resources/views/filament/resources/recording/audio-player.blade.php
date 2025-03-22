@php
    use Illuminate\Support\Facades\Storage;
    
    $record = $getRecord();
    $audioUrl = $record ? Storage::disk('public')->url($record->file_path) : null;
    $fileName = $record ? $record->file_name : 'audio';
@endphp

<div class="space-y-4">
    @if($audioUrl)
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <!-- Audio Player -->
            <div class="mb-4">
                <audio controls class="w-full" preload="metadata">
                    <source src="{{ $audioUrl }}" type="{{ $record->mime_type }}">
                    Tu navegador no soporta la reproducción de audio.
                </audio>
            </div>
            
            <!-- Download Button -->
            <div class="flex justify-end">
                <a href="{{ $audioUrl }}" 
                   download="{{ $fileName }}" 
                   class="inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button dark:focus:ring-offset-0 h-9 px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 filament-page-button-action">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <span>Descargar Audio</span>
                </a>
            </div>
        </div>
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-center text-gray-500 dark:text-gray-400">
            No hay archivo de audio disponible
        </div>
    @endif
</div>
