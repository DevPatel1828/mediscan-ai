<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    
    <!-- LEFT COLUMN: Upload Control & Historical Logs (Col span 1) -->
    <div class="space-y-6 lg:col-span-1">
        
        <!-- Upload Card -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm"
             x-data="{ isDragging: false }">
            <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white mb-4">Prescription Scanner</h3>
            
            <form wire:submit.prevent="initiateScan" class="space-y-4">
                <!-- Language Picker -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Target Language</label>
                    <select wire:model="scanLanguage" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm">
                        <option value="English">English</option>
                        <option value="Hindi">हिन्दी — Hindi</option>
                        <option value="Marathi">मराठी — Marathi</option>
                        <option value="Gujarati">ગુજરાતી — Gujarati</option>
                        <option value="Tamil">தமிழ் — Tamil</option>
                        <option value="Telugu">తెలుగు — Telugu</option>
                        <option value="Bengali">বাংলা — Bengali</option>
                        <option value="Kannada">ಕನ್ನಡ — Kannada</option>
                        <option value="Punjabi">ਪੰਜਾਬੀ — Punjabi</option>
                        <option value="Malayalam">മലയാളം — Malayalam</option>
                        <option value="Urdu">اردو — Urdu</option>
                        <option value="Spanish">Español — Spanish</option>
                        <option value="French">Français — French</option>
                        <option value="German">Deutsch — German</option>
                    </select>
                </div>

                <!-- Dropzone Area -->
                <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all duration-300 flex flex-col items-center justify-center cursor-pointer"
                     :class="isDragging ? 'border-brand-primary bg-brand-primary/5' : 'border-slate-200/50 dark:border-slate-800/50 hover:border-brand-primary/50'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                     @click="$refs.fileInput.click()">
                     
                    <input type="file" x-ref="fileInput" wire:model="prescriptionImage" class="hidden" accept="image/*,application/pdf" capture="environment">
                    
                    <div class="text-3xl mb-2">📷</div>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Drag & drop or browse</span>
                    <span class="text-[10px] text-slate-400 mt-1">Supports JPG, PNG, WEBP, PDF (Max 10MB)</span>
                    <span class="text-[10px] text-brand-primary font-bold mt-2">Deducts 20 Credits</span>
                </div>

                <!-- Livewire file upload loading indicator -->
                <div wire:loading wire:target="prescriptionImage" class="w-full text-center text-xs text-brand-primary font-semibold">
                    Uploading image to server...
                </div>

                <!-- Image Preview -->
                @if($prescriptionImage && !$isProcessing)
                    <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ $prescriptionImage->temporaryUrl() }}" class="w-full h-32 object-cover">
                        <button type="button" @click="$refs.fileInput.value = ''; @this.set('prescriptionImage', null)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md">✕</button>
                    </div>

                    <button type="submit" class="w-full py-3 bg-brand-primary hover:bg-blue-600 text-white rounded-xl font-medium shadow-lg shadow-brand-primary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        🔍 Scan & Analyze
                    </button>
                @endif
            </form>
        </div>

        <!-- Scan History Sidebar -->
        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
            <h3 class="font-display font-bold text-md text-slate-900 dark:text-white mb-4">Scan History</h3>
            <ul class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @forelse($history as $item)
                    <li>
                        <button wire:click="loadScan({{ $item->id }})" class="w-full text-left p-2.5 rounded-xl border border-slate-100 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-all flex items-center gap-3 {{ $prescriptionId === $item->id ? 'border-brand-primary bg-brand-primary/5' : '' }}">
                            <div class="w-8 h-8 rounded-lg overflow-hidden border border-slate-200/50 dark:border-slate-800/50 flex-shrink-0">
                                <img src="{{ $item->image_path }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">Scan #{{ $item->id }}</p>
                                <span class="text-[10px] text-slate-400 block">{{ $item->created_at->format('M d, g:i A') }}</span>
                            </div>
                        </button>
                    </li>
                @empty
                    <li class="text-center py-6 text-xs text-slate-400">No previous scans.</li>
                @endforelse
            </ul>
        </div>

    </div>

    <!-- RIGHT COLUMN: Real-Time Scanner Status & Final Results (Col span 3) -->
    <div class="lg:col-span-3 space-y-6">

        @if($isProcessing)
            <!-- ═══════════ REAL-TIME WEBSOCKET STATUS CARD ═══════════ -->
            <div class="p-8 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm flex flex-col items-center justify-center min-h-[50vh]">
                <div class="relative w-24 h-24 mb-6">
                    <!-- Spinning indicator -->
                    <div class="absolute inset-0 rounded-full border-4 border-slate-200 dark:border-slate-800"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-brand-primary border-t-transparent animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center text-2xl animate-pulse-slow">🔬</div>
                </div>

                <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white mb-2">Analyzing Prescription</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">FastAPI Python backend processing neural pipeline...</p>

                <!-- Progress Bar -->
                <div class="w-full max-w-md bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-6">
                    <div class="bg-brand-primary h-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                </div>

                <!-- Steps Log -->
                <div class="w-full max-w-sm space-y-3">
                    <div class="flex items-center justify-between text-sm {{ $progress >= 20 ? 'text-brand-primary font-semibold' : 'text-slate-400' }}">
                        <span>📤 Uploading to AI Engine</span>
                        <span>{{ $progress >= 20 ? '✓' : '...' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm {{ $progress >= 40 ? 'text-brand-primary font-semibold' : 'text-slate-400' }}">
                        <span>🎛️ Image Enhancement (OpenCV)</span>
                        <span>{{ $progress >= 40 ? '✓' : ($progress === 20 ? '●' : '...') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm {{ $progress >= 60 ? 'text-brand-primary font-semibold' : 'text-slate-400' }}">
                        <span>📝 Text Line Extraction (EasyOCR)</span>
                        <span>{{ $progress >= 60 ? '✓' : ($progress === 40 ? '●' : '...') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm {{ $progress >= 80 ? 'text-brand-primary font-semibold' : 'text-slate-400' }}">
                        <span>🛡️ Medicine Verification (FAISS & LLM)</span>
                        <span>{{ $progress >= 80 ? '✓' : ($progress === 60 ? '●' : '...') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm {{ $progress >= 100 ? 'text-brand-primary font-semibold' : 'text-slate-400' }}">
                        <span>✅ Final Report Rendering</span>
                        <span>{{ $progress >= 100 ? '✓' : ($progress === 80 ? '●' : '...') }}</span>
                    </div>
                </div>
            </div>
        @elseif($scanStatus === 'completed' && $ocrResult)
            <!-- ═══════════ DETAILED SCAN RESULTS DISPLAY ═══════════ -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                
                <!-- Prescription Image Side (2 cols) -->
                <div class="md:col-span-2 space-y-4">
                    <div class="rounded-2xl overflow-hidden border border-slate-200/50 dark:border-slate-800/50 bg-slate-100 dark:bg-slate-950 p-2 shadow-sm">
                        <img src="{{ $imageUrl }}" class="w-full h-auto rounded-xl max-h-[60vh] object-contain">
                    </div>
                    <div class="text-xs text-slate-400 text-center">
                        Uploaded image file path: <span class="font-mono">{{ $imagePath }}</span>
                    </div>
                </div>

                <!-- Analysis Details Side (3 cols) -->
                <div class="md:col-span-3 space-y-6">
                    
                    <!-- Confidence score & Metadata -->
                    <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">AI Confidence Score</span>
                            <span class="font-display font-extrabold text-3xl {{ ($ocrResult['confidence_score'] ?? 100) >= 80 ? 'text-green-500' : 'text-yellow-500' }}">
                                {{ $ocrResult['confidence_score'] ?? 'N/A' }}%
                            </span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block text-right">Language</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white block text-right">✓ {{ $scanLanguage }}</span>
                        </div>
                    </div>

                    <!-- Low Confidence Warning -->
                    @if(($ocrResult['confidence_score'] ?? 100) < 80)
                        <div class="p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-xs flex gap-3">
                            <span class="text-lg">⚠️</span>
                            <div>
                                <strong class="font-semibold block mb-0.5">Low Recognition Confidence</strong>
                                The system encountered difficulty reading parts of the prescription handwriting. Please double check all dosages and medicines with your doctor.
                            </div>
                        </div>
                    @endif

                    <!-- Medical Disclaimer -->
                    <div class="p-4 rounded-xl bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-xs flex gap-3">
                        <span class="text-lg">⚕️</span>
                        <div>
                            <strong class="font-semibold block mb-0.5">Medical Disclaimer</strong>
                            This analysis is for educational and literacy purposes only. Never adjust medication dosages or start/stop any treatment without consulting a licensed physician.
                        </div>
                    </div>

                    <!-- Extracted Medicine list -->
                    <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm space-y-4">
                        <h3 class="font-display font-bold text-md text-slate-950 dark:text-white border-b border-slate-200/50 dark:border-slate-800/50 pb-3 flex items-center gap-2">
                            <span>💊</span> Detected Medicines & Dosages
                        </h3>
                        
                        <div class="space-y-4 divide-y divide-slate-100 dark:divide-slate-800/50">
                            @forelse($ocrResult['medicines'] ?? [] as $idx => $med)
                                <div class="pt-4 {{ $idx === 0 ? 'pt-0' : '' }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-slate-950 dark:text-white text-base">{{ $med['name'] }}</h4>
                                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-brand-primary/10 text-brand-primary font-medium">
                                            {{ $med['frequency'] ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 font-medium"><span class="text-slate-500">Generic:</span> {{ $med['composition'] ?? 'N/A' }}</p>
                                    
                                    <div class="grid grid-cols-2 gap-4 text-xs mt-3 bg-slate-50/50 dark:bg-slate-950/20 p-3 rounded-xl">
                                        <div>
                                            <span class="text-slate-400 block mb-0.5">Dosage</span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $med['dosage'] ?? 'As directed' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block mb-0.5">Duration</span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $med['duration'] ?? 'As directed' }}</span>
                                        </div>
                                        <div class="col-span-2 pt-2 border-t border-slate-200/30 dark:border-slate-800/30">
                                            <span class="text-slate-400 block mb-0.5">Instructions</span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $med['instructions'] ?? 'As directed by doctor' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-slate-400 text-xs py-4">No medicines recognized in this scan.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- General Doctor Instructions -->
                    @if(!empty($ocrResult['doctor_instructions']))
                        <div class="p-6 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm">
                            <h3 class="font-display font-bold text-sm text-slate-950 dark:text-white border-b border-slate-200/50 dark:border-slate-800/50 pb-2 mb-3">
                                👨‍⚕️ General Doctor Notes
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                                {{ $ocrResult['doctor_instructions'] }}
                            </p>
                        </div>
                    @endif

                    <!-- Action panel -->
                    <div class="flex flex-wrap gap-3" x-data="{ speaking: false }">
                        <button type="button" @click="copyResults()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold flex items-center gap-2">
                            <span>📋</span> Copy Text
                        </button>
                        <button type="button" @click="speakResults()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold flex items-center gap-2" :class="speaking ? 'border-brand-primary text-brand-primary bg-brand-primary/5' : ''">
                            <span x-show="!speaking">🔊 Read Aloud</span>
                            <span x-show="speaking" x-cloak>⏹ Stop Audio</span>
                        </button>
                        <button type="button" @click="downloadPDF()" class="px-4 py-2.5 rounded-xl bg-brand-primary hover:bg-blue-600 text-white text-xs font-semibold flex items-center gap-2">
                            <span>⬇️</span> Download Report
                        </button>
                    </div>

                </div>

            </div>
        @else
            <!-- Placeholder Empty State -->
            <div class="p-8 rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 shadow-sm flex flex-col items-center justify-center min-h-[50vh] text-center">
                <span class="text-6xl mb-4 animate-bounce-slow">📑</span>
                <h3 class="font-display font-bold text-lg text-slate-900 dark:text-white">Ready for Scanning</h3>
                <p class="text-sm text-slate-400 max-w-sm mt-2">Upload a doctor's prescription image or document on the left to begin real-time neural OCR translation</p>
            </div>
        @endif

    </div>

    <!-- ═══════════ JS LOGIC FOR WEBSOCKET & TTS ═══════════ -->
    <script>
        document.addEventListener('livewire:init', () => {
            let ws = null;
            
            // Listen for scan start event from Livewire
            Livewire.on('start-websocket-scan', (event) => {
                // Livewire v3 passes dispatched event data as an object directly, but let's handle array/detail fallbacks safely
                const data = (event && typeof event === 'object' && !Array.isArray(event)) ? event : (Array.isArray(event) ? event[0] : (event && event.detail ? event.detail : event));
                
                // Establish connection to FastAPI WebSockets endpoint
                const wsUrl = `{{ env('FASTAPI_WS_URL', 'ws://localhost:8000') }}/ws/scan?prescription_id=${data.prescriptionId}&image_path=${encodeURIComponent(data.imagePath)}&language=${data.language}`;
                console.log("Connecting to WebSocket AI scanner:", wsUrl);
                
                ws = new WebSocket(wsUrl);
                
                ws.onmessage = (msg) => {
                    const update = JSON.parse(msg.data);
                    console.log("WebSocket AI status update:", update);
                    
                    if (update.status === 'enhancing') {
                        @this.set('scanStatus', 'enhancing');
                        @this.set('progress', 40);
                    } else if (update.status === 'extracting') {
                        @this.set('scanStatus', 'extracting');
                        @this.set('progress', 60);
                    } else if (update.status === 'validating') {
                        @this.set('scanStatus', 'validating');
                        @this.set('progress', 80);
                    } else if (update.status === 'completed') {
                        // Scan complete: notify Livewire to load final DB contents
                        @this.call('scanCompleted', data.prescriptionId);
                        ws.close();
                    } else if (update.status === 'failed') {
                        // Scan failed: notify Livewire
                        @this.call('scanFailed', update.error || 'Pipeline error occurred.');
                        ws.close();
                    }
                };
                
                ws.onerror = (err) => {
                    console.error("WebSocket connection error:", err);
                    @this.call('scanFailed', 'Connection to Python AI service failed.');
                };
            });
        });

        // Copy Results to Clipboard
        function copyResults() {
            const container = document.createElement('textarea');
            let text = "MediScan AI 2.0 Prescription Report\n\n";
            
            const meds = @json($ocrResult['medicines'] ?? []);
            meds.forEach(med => {
                text += `Medicine: ${med.name}\nGeneric: ${med.composition}\nDosage: ${med.dosage}\nDuration: ${med.duration}\nInstructions: ${med.instructions}\n\n`;
            });
            
            const docNotes = "{{ $ocrResult['doctor_instructions'] ?? '' }}";
            if (docNotes) {
                text += `Doctor Notes: ${docNotes}\n`;
            }
            
            container.value = text;
            document.body.appendChild(container);
            container.select();
            document.execCommand('copy');
            document.body.removeChild(container);
            
            window.showToast('📋 Prescription report copied to clipboard!');
        }

        // Web Speech Synthesis (TTS) Reader
        let ttsUtterance = null;
        function speakResults() {
            const self = this;
            if (window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                window.showToast('⏹ Stopped audio reader.');
                return;
            }

            let text = "Here are your detected medications. ";
            const meds = @json($ocrResult['medicines'] ?? []);
            meds.forEach(med => {
                text += `Take ${med.name}. ${med.instructions}. `;
            });
            
            const docNotes = "{{ $ocrResult['doctor_instructions'] ?? '' }}";
            if (docNotes) {
                text += `Doctor notes say: ${docNotes}`;
            }

            // Remove emojis from reading
            const cleanText = text.replace(/[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\u{1F600}-\u{1F64F}\u{1F680}-\u{1F6FF}\u{1FA70}-\u{1FAFF}]/gu, '');
            
            // Map selected target language to browser speech engine codes
            const langMap = {
                'english': 'en-US', 'hindi': 'hi-IN', 'gujarati': 'gu-IN', 'marathi': 'mr-IN',
                'tamil': 'ta-IN', 'telugu': 'te-IN', 'bengali': 'bn-IN', 'kannada': 'kn-IN',
                'punjabi': 'pa-IN', 'malayalam': 'ml-IN', 'urdu': 'ur-PK', 'spanish': 'es-ES',
                'french': 'fr-FR', 'german': 'de-DE'
            };
            const currentLang = "{{ strtolower($scanLanguage) }}";
            
            ttsUtterance = new SpeechSynthesisUtterance(cleanText);
            ttsUtterance.lang = langMap[currentLang] || 'en-US';
            ttsUtterance.rate = 0.90;

            window.speechSynthesis.speak(ttsUtterance);
            window.showToast('🔊 Audio reader started...');
        }

        // PDF download redirect
        function downloadPDF() {
            window.location.href = `/reports/pdf/{{ $prescriptionId }}`;
        }
    </script>
</div>
