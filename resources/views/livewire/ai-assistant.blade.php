<div class="flex flex-col h-[80vh] rounded-2xl glass-card bg-white/60 dark:bg-slate-900/60 border border-slate-200/50 dark:border-slate-800/50 overflow-hidden shadow-sm"
     x-data="chatSystem()">
    
    <!-- ═══════════ MEDICAL DISCLAIMER HEADER ═══════════ -->
    <div class="p-4 bg-brand-primary/10 border-b border-brand-primary/20 text-brand-primary text-xs flex gap-3 items-center">
        <span class="text-lg">⚕️</span>
        <div>
            <strong class="font-semibold block">Medical Disclaimer</strong>
            This assistant is an AI educational guide. It cannot provide medical diagnoses, prescribe treatments, or substitute professional medical advice. Always consult a physician.
        </div>
    </div>

    <!-- ═══════════ CHAT MESSAGE LIST viewport ═══════════ -->
    <div class="flex-1 p-6 overflow-y-auto space-y-4" x-ref="chatBox">
        <!-- Static / Historical Messages -->
        <template x-for="(msg, index) in messages" :key="index">
            <div class="flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                <div class="max-w-[75%] px-4 py-3 rounded-2xl text-sm leading-relaxed"
                     :class="msg.role === 'user' 
                        ? 'bg-brand-primary text-white rounded-br-none shadow-md shadow-brand-primary/15' 
                        : 'bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 rounded-bl-none border border-slate-200/50 dark:border-slate-800/50'">
                    <p class="whitespace-pre-wrap font-medium" x-html="formatMarkdown(msg.text)"></p>
                </div>
                <span class="text-[10px] text-slate-400 mt-1 px-1 font-semibold" 
                      x-text="msg.role === 'user' ? 'Patient' : 'MediScan AI'"></span>
            </div>
        </template>

        <!-- Current Streaming AI Response Bubble -->
        <div class="flex flex-col items-start" x-show="isStreaming" x-cloak>
            <div class="max-w-[75%] px-4 py-3 rounded-2xl text-sm leading-relaxed bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 rounded-bl-none border border-slate-200/50 dark:border-slate-800/50">
                <p class="whitespace-pre-wrap font-medium" x-html="formatMarkdown(streamingText)"></p>
                <div class="flex items-center gap-1.5 mt-2" x-show="streamingText === 'Thinking...'">
                    <div class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-bounce"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-bounce [animation-delay:0.2s]"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-bounce [animation-delay:0.4s]"></div>
                </div>
            </div>
            <span class="text-[10px] text-slate-400 mt-1 px-1 font-semibold">MediScan AI</span>
        </div>
    </div>

    <!-- ═══════════ INPUT CONTROL BAR ═══════════ -->
    <div class="p-4 border-t border-slate-200/50 dark:border-slate-800/50 bg-white/30 dark:bg-slate-950/20">
        <form @submit.prevent="sendMessage()" class="flex gap-3 items-center">
            <input type="text" x-model="userInput" :disabled="isStreaming"
                   class="flex-1 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-sm"
                   placeholder="Ask about medicine uses, dosages, side effects, or warning details...">
            
            <button type="submit" :disabled="isStreaming || !userInput.trim()"
                    class="py-3 px-6 bg-brand-primary hover:bg-blue-600 disabled:bg-slate-400 text-white rounded-xl font-semibold text-sm shadow-lg shadow-brand-primary/30 active:scale-[0.98] transition-all flex items-center gap-2">
                <span>⚡ Send</span>
            </button>
        </form>
        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2 px-1">
            <span>Deducts 15 credits per query</span>
            <span class="font-bold text-brand-primary">Groq AI Assistant</span>
        </div>
    </div>

    <!-- ═══════════ ALPINE CHAT CONTROLLER ═══════════ -->
    <script>
        function chatSystem() {
            return {
                userInput: '',
                messages: [],
                isStreaming: false,
                streamingText: '',
                ws: null,

                init() {
                    // Populate initial history from Livewire data
                    const rawHistory = @json($chatHistory);
                    rawHistory.forEach(item => {
                        this.messages.push({ role: 'user', text: item.message });
                        if (item.response) {
                            this.messages.push({ role: 'assistant', text: item.response });
                        }
                    });

                    // Scroll to bottom
                    this.$nextTick(() => this.scrollToBottom());
                    this.connectWebSocket();
                },

                connectWebSocket() {
                    const wsUrl = `{{ env('FASTAPI_WS_URL', 'ws://localhost:8000') }}/ws/chat?user_id={{ auth()->id() }}`;
                    console.log("Connecting to WebSocket AI Chat:", wsUrl);

                    this.ws = new WebSocket(wsUrl);

                    this.ws.onmessage = (event) => {
                        const data = JSON.parse(event.data);
                        
                        if (data.status === 'chunk') {
                            if (this.streamingText === 'Thinking...') {
                                this.streamingText = '';
                            }
                            this.streamingText += data.text;
                            this.scrollToBottom();
                        } else if (data.status === 'completed') {
                            // Message finished, append response
                            this.messages.push({ role: 'assistant', text: this.streamingText });
                            this.isStreaming = false;
                            this.streamingText = '';
                            
                            // Trigger Livewire to refresh credits and database history log
                            @this.call('refreshCreditsAndHistory');
                            this.scrollToBottom();
                        } else if (data.status === 'error') {
                            window.showToast(data.message, 'error');
                            this.isStreaming = false;
                            this.streamingText = '';
                        }
                    };

                    this.ws.onclose = () => {
                        console.log("WebSocket chat disconnected. Reconnecting in 5s...");
                        setTimeout(() => this.connectWebSocket(), 5000);
                    };

                    this.ws.onerror = (err) => {
                        console.error("Chat WebSocket error:", err);
                        this.isStreaming = false;
                    };
                },

                sendMessage() {
                    if (this.userInput.trim() === '') return;
                    
                    const text = this.userInput;
                    this.messages.push({ role: 'user', text: text });
                    this.userInput = '';
                    
                    this.isStreaming = true;
                    this.streamingText = 'Thinking...';
                    
                    this.$nextTick(() => this.scrollToBottom());

                    // Send payload over WebSocket
                    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                        this.ws.send(JSON.stringify({ message: text }));
                    } else {
                        window.showToast("⚠️ Chat service offline. Reconnecting...", "error");
                        this.isStreaming = false;
                        this.streamingText = '';
                    }
                },

                scrollToBottom() {
                    const el = this.$refs.chatBox;
                    if (el) {
                        el.scrollTop = el.scrollHeight;
                    }
                },

                formatMarkdown(text) {
                    if (!text) return '';
                    // Clean and replace markdown headings or bold text
                    return text
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/^- (.*)$/g, '• $1');
                }
            };
        }
    </script>
</div>
