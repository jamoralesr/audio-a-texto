<x-filament::page>
    <div class="space-y-6" x-data="audioRecorder" x-init="init">
        <!-- Recording Controls -->
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <h2 class="text-xl font-bold mb-4">{{ __('Grabación de Audio') }}</h2>
            
            <div class="flex flex-col space-y-4">
                <!-- Audio Visualization -->
                <div id="waveform" class="w-full h-24 bg-gray-100 dark:bg-gray-700 rounded-lg"></div>
                
                <!-- Recording Timer -->
                <div class="text-center text-2xl font-mono" x-text="formattedTime">
                    00:00
                </div>
                
                <!-- Recording Controls -->
                <div class="flex justify-center space-x-4">
                    <x-filament::button
                        color="danger"
                        icon="heroicon-o-microphone"
                        x-on:click="toggleRecording"
                        x-text="isRecording ? 'Grabando...' : 'Grabar'"
                        x-bind:class="{'bg-red-600': isRecording}"
                    >
                        {{ __('Grabar') }}
                    </x-filament::button>
                    
                    <x-filament::button
                        color="warning"
                        icon="heroicon-o-pause"
                        x-on:click="togglePause"
                        x-text="isPaused ? 'Continuar' : 'Pausar'"
                        x-bind:disabled="!isRecording"
                    >
                        {{ __('Pausar') }}
                    </x-filament::button>
                    
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-stop"
                        x-on:click="stopRecording"
                        x-bind:disabled="!isRecording"
                    >
                        {{ __('Detener') }}
                    </x-filament::button>
                </div>
                
                <!-- Audio Preview (after recording) -->
                <div x-show="audioUrl" x-transition class="mt-4">
                    <audio x-ref="audioPlayer" controls class="w-full"></audio>
                </div>
            </div>
        </div>
        
        <!-- Recording Form -->
        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <h2 class="text-xl font-bold mb-4">{{ __('Detalles de la Grabación') }}</h2>
            
            <form wire:submit="saveRecording">
                {{ $this->form }}
                
                <div class="flex justify-end mt-4 space-x-2">
                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="resetRecording"
                    >
                        {{ __('Cancelar') }}
                    </x-filament::button>
                    
                    <x-filament::button
                        type="submit"
                        color="success"
                        x-bind:disabled="!audioUrl"
                    >
                        {{ __('Guardar Grabación') }}
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://unpkg.com/wavesurfer.js@6.6.3/dist/wavesurfer.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('audioRecorder', () => ({
                mediaRecorder: null,
                audioChunks: [],
                stream: null,
                wavesurfer: null,
                startTime: null,
                timerInterval: null,
                elapsedTime: 0,
                isRecording: false,
                isPaused: false,
                audioBlob: null,
                audioUrl: null,
                formattedTime: '00:00',
                
                init() {
                    // Inicializar WaveSurfer
                    this.wavesurfer = WaveSurfer.create({
                        container: '#waveform',
                        waveColor: '#4f46e5',
                        progressColor: '#818cf8',
                        cursorColor: '#ef4444',
                        barWidth: 2,
                        barRadius: 3,
                        cursorWidth: 1,
                        height: 80,
                        barGap: 3
                    });
                    
                    // Escuchar eventos de Livewire
                    this.$wire.$on('reset-audio-recorder', () => this.reset());
                    
                    // Escuchar cambios en las propiedades de Livewire
                    this.$watch('isRecording', (value) => {
                        if (value && !this.mediaRecorder) {
                            this.startRecording();
                        } else if (!value && this.mediaRecorder) {
                            this.stopRecording();
                        }
                    });
                    
                    this.$watch('isPaused', (value) => {
                        if (this.mediaRecorder) {
                            if (value && this.mediaRecorder.state === 'recording') {
                                this.mediaRecorder.pause();
                                clearInterval(this.timerInterval);
                            } else if (!value && this.mediaRecorder.state === 'paused') {
                                this.mediaRecorder.resume();
                                this.startTimer();
                            }
                        }
                    });
                },
                
                formatTime(seconds) {
                    const minutes = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },
                
                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (!this.isPaused) {
                            this.elapsedTime = (Date.now() - this.startTime) / 1000;
                            this.formattedTime = this.formatTime(this.elapsedTime);
                            this.$wire.updateRecordingTime(this.elapsedTime);
                        }
                    }, 1000);
                },
                
                async startRecording() {
                    try {
                        // Reiniciar grabación anterior
                        this.audioChunks = [];
                        this.audioUrl = null;
                        
                        // Obtener permiso del micrófono
                        this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        
                        // Crear media recorder
                        this.mediaRecorder = new MediaRecorder(this.stream);
                        
                        // Configurar manejadores de eventos
                        this.mediaRecorder.ondataavailable = (event) => {
                            if (event.data.size > 0) {
                                this.audioChunks.push(event.data);
                            }
                        };
                        
                        this.mediaRecorder.onstop = () => {
                            // Crear blob de los fragmentos grabados
                            this.audioBlob = new Blob(this.audioChunks, { type: 'audio/wav' });
                            this.audioUrl = URL.createObjectURL(this.audioBlob);
                            
                            // Configurar reproductor de audio
                            this.$refs.audioPlayer.src = this.audioUrl;
                            
                            // Cargar audio en wavesurfer
                            this.wavesurfer.load(this.audioUrl);
                            
                            // Detener todas las pistas
                            this.stream.getTracks().forEach(track => track.stop());
                            
                            // Enviar audio al servidor
                            this.uploadAudio();
                        };
                        
                        // Iniciar grabación
                        this.mediaRecorder.start(100);
                        this.isRecording = true;
                        this.isPaused = false;
                        
                        // Iniciar temporizador
                        this.startTime = Date.now();
                        this.startTimer();
                        
                        // Actualizar estado en Livewire
                        this.$wire.toggleRecording();
                        
                    } catch (error) {
                        console.error('Error al acceder al micrófono:', error);
                        alert('No se pudo acceder al micrófono. Por favor, verifica los permisos.');
                        this.isRecording = false;
                        this.$wire.toggleRecording();
                    }
                },
                
                toggleRecording() {
                    if (!this.isRecording) {
                        this.startRecording();
                    } else {
                        this.stopRecording();
                    }
                },
                
                togglePause() {
                    if (this.isRecording) {
                        this.isPaused = !this.isPaused;
                        this.$wire.togglePause();
                    }
                },
                
                stopRecording() {
                    if (this.mediaRecorder && (this.mediaRecorder.state === 'recording' || this.mediaRecorder.state === 'paused')) {
                        this.mediaRecorder.stop();
                        this.isRecording = false;
                        this.isPaused = false;
                        
                        // Detener temporizador
                        clearInterval(this.timerInterval);
                        
                        // Actualizar estado en Livewire
                        this.$wire.stopRecording();
                    }
                },
                
                reset() {
                    if (this.mediaRecorder && (this.mediaRecorder.state === 'recording' || this.mediaRecorder.state === 'paused')) {
                        this.mediaRecorder.stop();
                    }
                    
                    this.isRecording = false;
                    this.isPaused = false;
                    this.elapsedTime = 0;
                    this.formattedTime = '00:00';
                    this.audioBlob = null;
                    this.audioUrl = null;
                    
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                    }
                    
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }
                    
                    // Reiniciar wavesurfer
                    this.wavesurfer.empty();
                },
                
                uploadAudio() {
                    if (!this.audioBlob) return;
                    
                    // Convertir el blob a base64
                    const reader = new FileReader();
                    reader.readAsDataURL(this.audioBlob);
                    reader.onloadend = () => {
                        const base64data = reader.result;
                        // Enviar al componente Livewire
                        this.$wire.processAudioUpload(base64data);
                    };
                }
            }));
        });
    </script>
    @endpush
</x-filament::page>
