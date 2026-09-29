<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediScan AI 2.0 — Enterprise AI Prescription Analysis Platform</title>
    
    <!-- PWA manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563EB">
    <link rel="apple-touch-icon" href="{{ asset('brand_logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-bg text-slate-200 min-h-screen font-sans relative overflow-x-hidden antialiased">
    <!-- Ambient glowing orbs -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="orb w-[30rem] h-[30rem] bg-brand-primary/10 top-[-10%] left-[-10%]"></div>
        <div class="orb w-[35rem] h-[35rem] bg-brand-secondary/15 bottom-[-10%] right-[-10%]"></div>
        <div class="orb w-96 h-96 bg-brand-accent/10 top-[30%] left-[40%]"></div>
    </div>

    <!-- ═══════════ HEADER / NAVIGATION ═══════════ -->
    <header class="sticky top-0 z-50 glass-card bg-slate-900/60 border-b border-slate-800/40 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('brand_logo.png') }}" alt="MediScan Logo" class="w-10 h-10 rounded-xl shadow-lg border border-brand-primary/20">
            <div>
                <h1 class="font-display font-bold text-lg leading-tight text-white tracking-tight">
                    MediScan <span class="text-brand-primary">AI 2.0</span>
                </h1>
                <span class="text-[10px] text-slate-400 block tracking-wider uppercase font-semibold">Enterprise SaaS</span>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-300">
            <a href="#features" class="hover:text-white transition-colors">Features</a>
            <a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a>
            <a href="#technology" class="hover:text-white transition-colors">AI Engine</a>
            <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
            <a href="#contact" class="hover:text-white transition-colors">Contact</a>
        </nav>

        <div class="flex items-center gap-4">
            @auth
                <a href="{{ url('dashboard') }}" class="px-5 py-2.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-primary/25 transition-all">
                    Go to Portal ➔
                </a>
            @else
                <a href="{{ url('login') }}" class="text-xs font-bold text-slate-300 hover:text-white transition-all">Sign In</a>
                <a href="{{ url('register') }}" class="px-5 py-2.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-primary/25 transition-all">
                    Create Account
                </a>
            @endauth
        </div>
    </header>

    <!-- ═══════════ HERO SECTION ═══════════ -->
    <section class="relative z-10 px-6 py-20 lg:py-32 max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-primary/10 text-brand-primary border border-brand-primary/20 tracking-wide uppercase">
                ⚡ Next-Gen Medical Vision
            </span>
            <h2 class="font-display font-extrabold text-4xl sm:text-5xl lg:text-6xl text-white leading-tight">
                Decipher Doctor <br>
                <span class="bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent">Prescriptions Instantly</span>
            </h2>
            <p class="text-sm sm:text-base text-slate-400 leading-relaxed font-medium max-w-lg">
                MediScan AI 2.0 uses neural EasyOCR pre-processing, clinical FAISS similarity vector matching, and Groq-powered medical validation to turn messy doctor abbreviations into plain patient guides in real time.
            </p>
            
            <div class="flex flex-wrap gap-4 pt-4">
                @auth
                    <a href="{{ url('scanner') }}" class="px-8 py-3.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] transition-all">
                        Launch Scanner 📷
                    </a>
                @else
                    <a href="{{ url('register') }}" class="px-8 py-3.5 bg-brand-primary hover:bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-brand-primary/30 active:scale-[0.98] transition-all">
                        Get 5000 Daily Credits
                    </a>
                    <a href="{{ url('login') }}" class="px-8 py-3.5 border border-slate-700 hover:bg-slate-800/40 text-slate-200 rounded-xl font-bold transition-all">
                        Demo Account Login
                    </a>
                @endauth
            </div>
        </div>

        <!-- Simulated Portal Preview -->
        <div class="lg:block relative">
            <div class="absolute inset-0 bg-brand-primary/10 rounded-3xl filter blur-xl animate-pulse-slow"></div>
            <div class="relative glass-card bg-slate-900/70 border border-slate-800/50 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-850 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 font-mono tracking-widest">NEURAL SCANNER RUNNING</span>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-950 p-3 rounded-2xl border border-slate-900 flex items-center justify-center h-40">
                        <span class="text-xs text-slate-400 font-mono">[Prescription Image]</span>
                    </div>
                    <div class="space-y-3">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-500/10 text-green-500 border border-green-500/20">Confidence: 94.6%</span>
                        <h4 class="font-bold text-sm text-white">Azithral 500mg</h4>
                        <p class="text-[10px] text-slate-400"><span class="text-slate-500">Uses:</span> Bacterial infections.</p>
                        <p class="text-[10px] text-slate-400"><span class="text-slate-500">Dosage:</span> Take one tablet once daily (OD) for 5 days.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════ FEATURES SECTION ═══════════ -->
    <section id="features" class="relative z-10 px-6 py-24 max-w-6xl mx-auto border-t border-slate-800/40">
        <div class="text-center mb-16 space-y-3">
            <h3 class="font-display font-extrabold text-3xl text-white sm:text-4xl">Platform Features</h3>
            <p class="text-sm text-slate-400 max-w-xl mx-auto">MediScan AI 2.0 provides an enterprise suite of artificial intelligence tools for healthcare literacy.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Scan -->
            <div class="p-6 rounded-2xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm hover:scale-[1.02] transition-transform duration-300">
                <span class="text-4xl mb-4 block">📷</span>
                <h4 class="font-display font-bold text-lg text-white mb-2">Prescription Scanner</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">Supports smart drag-and-drop, camera scans, screenshot pastes, and PDF uploads. Processed in real time via WebSockets.</p>
            </div>
            <!-- Interaction -->
            <div class="p-6 rounded-2xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm hover:scale-[1.02] transition-transform duration-300">
                <span class="text-4xl mb-4 block">🛡️</span>
                <h4 class="font-display font-bold text-lg text-white mb-2">Drug Interaction Checker</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">Evaluate multi-drug interaction risk severities (Low, Medium, High, Critical) automatically against our medical interaction matrices.</p>
            </div>
            <!-- Chat -->
            <div class="p-6 rounded-2xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm hover:scale-[1.02] transition-transform duration-300">
                <span class="text-4xl mb-4 block">💬</span>
                <h4 class="font-display font-bold text-lg text-white mb-2">AI Health Assistant</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">ChatGPT-style patient assistant to clarify usages, dosages, side effects, and warning parameters (never issues medical diagnoses).</p>
            </div>
            <!-- Abbreviation -->
            <div class="p-6 rounded-2xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm hover:scale-[1.02] transition-transform duration-300">
                <span class="text-4xl mb-4 block">🧪</span>
                <h4 class="font-display font-bold text-lg text-white mb-2">Abbreviation Decoder</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">Translates medical abbreviations (like Rx, OD, BD, PRN, AC, PC, STAT) into plain English or native Indian and international languages.</p>
            </div>
            <!-- Multilingual -->
            <div class="p-6 rounded-2xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm hover:scale-[1.02] transition-transform duration-300">
                <span class="text-4xl mb-4 block">🌐</span>
                <h4 class="font-display font-bold text-lg text-white mb-2">Multi-Language Translate</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">Read reports and AI assistant chat responses in English, Hindi, Gujarati, Marathi, Tamil, Telugu, Bengali, Kannada, Punjabi, Malayalam, or Urdu.</p>
            </div>
            <!-- TTS -->
            <div class="p-6 rounded-2xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm hover:scale-[1.02] transition-transform duration-300">
                <span class="text-4xl mb-4 block">🔊</span>
                <h4 class="font-display font-bold text-lg text-white mb-2">Voice Assistant TTS</h4>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">Listen to your dosage schedule, side effects, and warnings read aloud in your native language with a built-in text-to-speech engine.</p>
            </div>
        </div>
    </section>

    <!-- ═══════════ HOW IT WORKS SECTION ═══════════ -->
    <section id="how-it-works" class="relative z-10 px-6 py-24 max-w-6xl mx-auto border-t border-slate-800/40">
        <div class="text-center mb-16 space-y-3">
            <h3 class="font-display font-extrabold text-3xl text-white sm:text-4xl">How It Works</h3>
            <p class="text-sm text-slate-400 max-w-xl mx-auto">Deciphering handwritten scripts is done in four steps through our high-performance pipeline.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-primary/10 border border-brand-primary/25 text-brand-primary font-display font-bold text-lg flex items-center justify-center mx-auto">1</div>
                <h4 class="font-bold text-white text-base">Image Upload</h4>
                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">Drop an image or take a snapshot with your mobile camera. Supports PDFs and screenshots.</p>
            </div>
            <div class="text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-secondary/10 border border-brand-secondary/25 text-brand-secondary font-display font-bold text-lg flex items-center justify-center mx-auto">2</div>
                <h4 class="font-bold text-white text-base">OpenCV Enhancement</h4>
                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">Applies noise reduction, rotation alignment, and CLAHE contrast adjustments for better text readability.</p>
            </div>
            <div class="text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-brand-accent/10 border border-brand-accent/25 text-brand-accent font-display font-bold text-lg flex items-center justify-center mx-auto">3</div>
                <h4 class="font-bold text-white text-base">OCR & FAISS Lookup</h4>
                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">Extracts text via neural OCR. Matches misread names against database using FAISS vector search.</p>
            </div>
            <div class="text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-green-500/10 border border-green-500/25 text-green-500 font-display font-bold text-lg flex items-center justify-center mx-auto">4</div>
                <h4 class="font-bold text-white text-base">Plain Language Output</h4>
                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">Displays a plain guide showing generic name, usage, side effects, and warning parameters.</p>
            </div>
        </div>
    </section>

    <!-- ═══════════ PRICING SECTION ═══════════ -->
    <section id="pricing" class="relative z-10 px-6 py-24 max-w-4xl mx-auto border-t border-slate-800/40">
        <div class="text-center mb-16 space-y-3">
            <h3 class="font-display font-extrabold text-3xl text-white sm:text-4xl">Pricing Plans</h3>
            <p class="text-sm text-slate-400">Choose the plan that suits your personal or enterprise needs.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Free Plan -->
            <div class="p-8 rounded-3xl glass-card bg-slate-900/40 border border-slate-800/40 shadow-sm flex flex-col justify-between hover:scale-[1.02] transition-transform duration-300">
                <div class="space-y-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Free Tier</span>
                    <h4 class="font-display font-extrabold text-3xl text-white">$0 <span class="text-sm font-normal text-slate-400">/ forever</span></h4>
                    <p class="text-xs text-slate-400 font-medium">Ideal for individuals testing out the AI OCR capabilities.</p>
                    <ul class="space-y-2 text-xs font-medium text-slate-300 pt-4 border-t border-slate-800/50">
                        <li>⚡ 5000 Daily Credits</li>
                        <li>📷 Smart Drag & Drop scan (20 creds)</li>
                        <li>🔍 Medicine Database Search (5 creds)</li>
                        <li>🌐 Multilingual translation (5 creds)</li>
                        <li>🚪 Standard community support</li>
                    </ul>
                </div>
                <a href="{{ url('register') }}" class="w-full py-3 mt-8 border border-slate-700 hover:bg-slate-800/40 text-slate-200 rounded-xl text-xs font-bold transition-all text-center">
                    Get Started Free
                </a>
            </div>

            <!-- Premium Plan -->
            <div class="p-8 rounded-3xl glass-card bg-slate-900/60 border-2 border-brand-primary shadow-2xl flex flex-col justify-between hover:scale-[1.02] transition-transform duration-300 relative">
                <span class="absolute top-0 right-6 translate-y-[-50%] px-3 py-1 rounded-full text-[10px] font-bold bg-brand-primary text-white uppercase tracking-wider">Most Popular</span>
                
                <div class="space-y-4">
                    <span class="text-xs font-bold text-brand-primary uppercase tracking-wider">Premium Access</span>
                    <h4 class="font-display font-extrabold text-3xl text-white">$29 <span class="text-sm font-normal text-slate-400">/ month</span></h4>
                    <p class="text-xs text-slate-400 font-medium">Ideal for doctors, pharmacies, and high-frequency health users.</p>
                    <ul class="space-y-2 text-xs font-medium text-slate-300 pt-4 border-t border-slate-800/50">
                        <li>⚡ Unlimited Daily Credits</li>
                        <li>📷 Real-Time WebSocket OCR Scan (Fastest)</li>
                        <li>🛡️ Full Drug Interaction Checker</li>
                        <li>💬 AI Chat Health Assistant</li>
                        <li>🔊 Multi-Language Voice Reader (TTS)</li>
                        <li>📥 PDF, CSV & Excel reports export</li>
                    </ul>
                </div>
                <a href="{{ url('register') }}" class="w-full py-3.5 mt-8 bg-brand-primary hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-primary/25 transition-all text-center">
                    Upgrade to Premium
                </a>
            </div>
        </div>
    </section>

    <!-- ═══════════ CONTACT FORM SECTION ═══════════ -->
    <section id="contact" class="relative z-10 px-6 py-24 max-w-lg mx-auto border-t border-slate-800/40">
        <div class="p-8 rounded-2xl glass-card bg-slate-900/50 border border-slate-800/40 shadow-sm space-y-6">
            <div class="text-center space-y-2">
                <h3 class="font-display font-bold text-xl text-white">Contact Us</h3>
                <p class="text-xs text-slate-400">Have questions about MediScan AI 2.0? Reach out to us.</p>
            </div>

            <!-- Simulated Form -->
            <form onsubmit="event.preventDefault(); window.showToast('📧 Contact message logged successfully!', 'success'); this.reset();" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Your Name</label>
                    <input type="text" required class="w-full px-4 py-2.5 rounded-xl border border-slate-800 bg-slate-950/50 text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-xs" placeholder="Jane Doe">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-800 bg-slate-950/50 text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-xs" placeholder="jane@example.com">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Message</label>
                    <textarea required rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-800 bg-slate-950/50 text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 text-xs" placeholder="Describe your query..."></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-brand-primary hover:bg-blue-600 text-white rounded-xl font-semibold text-xs shadow-md transition-all">
                    Send Message
                </button>
            </form>
        </div>
    </section>

    <!-- ═══════════ FOOTER ═══════════ -->
    <footer class="relative z-10 py-12 px-6 border-t border-slate-800/40 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('brand_logo.png') }}" alt="MediScan Logo" class="w-8 h-8 rounded-lg">
                <span class="font-display font-semibold text-slate-300">MediScan AI 2.0</span>
            </div>
            <p>© 2026 MediScan AI. Built using Laravel 12, Livewire 3, Tailwind CSS & FastAPI. All rights reserved.</p>
            <div class="flex gap-4 font-semibold text-slate-400">
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
                <a href="#" onclick="event.preventDefault(); window.showToast('📋 Mock terms loaded.', 'success')" class="hover:text-white transition-colors">Terms</a>
            </div>
        </div>
    </footer>

    <!-- Toast Notification container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
    <script>
        window.showToast = function(msg, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-xl shadow-lg border backdrop-blur-md transition-all duration-300 transform translate-y-2 opacity-0 flex items-center gap-2.5 text-sm font-medium ${
                type === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-500' : 'bg-red-500/10 border-red-500/20 text-red-500'
            }`;
            toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> ${msg}`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('translate-y-2', 'opacity-0'); }, 10);
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3200);
        };
    </script>
</body>
</html>
