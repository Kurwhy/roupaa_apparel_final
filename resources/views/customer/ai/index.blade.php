<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>ROUPAA | AI Design Studio</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="{{ asset('js/tailwind-config/customer.js') }}"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ai-studio.css') }}">

    {{-- Proteksi halaman: harus login sebagai pelanggan --}}
    <script>
        (function() {
            const token = sessionStorage.getItem('access_token');
            const role = sessionStorage.getItem('user_role');
            if (!token) {
                window.location.href = '/login';
            } else if (role !== 'pelanggan') {
                window.location.href = '/';
            }
        })();
    </script>
</head>

<body
    class="font-body selection:bg-primary-container selection:text-on-primary-container bg-background text-on-background min-h-screen lg:h-screen overflow-x-hidden lg:overflow-hidden flex flex-col">

    @include('components.navbar')

    <main
        class="flex-grow pt-32 pb-8 px-4 md:px-8 relative overflow-y-auto overflow-x-hidden lg:overflow-hidden flex flex-col min-h-0 custom-scrollbar">
        <div class="absolute inset-0 industrial-grid pointer-events-none opacity-50 z-0"></div>
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[800px] h-[400px] bg-primary/5 blur-[120px] pointer-events-none z-0">
        </div>

        <div
            class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 relative z-10 flex-grow min-h-0">

            <div class="lg:col-span-8 flex flex-col min-h-[60vh] lg:min-h-0 lg:h-full gap-6">

                <header class="text-left border-l-[3px] border-primary pl-6 flex-shrink-0">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div>
                            <span
                                class="text-primary font-headline uppercase tracking-[0.2em] text-xs font-bold mb-2 block">Fitur
                                Eksklusif PRO</span>
                            <h1
                                class="text-4xl md:text-5xl font-headline font-black uppercase tracking-tight text-white leading-tight">
                                AI Design Studio</h1>
                            <p class="text-on-surface-variant text-sm mt-2 max-w-lg">Buat konsep desain apparel otomatis
                                hanya dengan mendeskripsikannya.</p>
                        </div>
                        <div
                            class="flex items-center gap-3 bg-surface-container-highest border border-outline-variant/30 px-4 py-2.5 md:px-5 md:py-3 rounded-xl shadow-lg">
                            <span id="credit_dot"
                                class="w-2.5 h-2.5 rounded-full {{ $creditsLeft > 0 ? 'bg-primary animate-pulse shadow-[0_0_10px_#f2ca50]' : 'bg-red-500' }}"></span>
                            <span id="credit_counter"
                                class="text-xs font-bold {{ $creditsLeft > 0 ? 'text-white' : 'text-red-400' }} uppercase tracking-widest">
                                {{ $creditsLeft }} Kredit Hari Ini
                            </span>
                        </div>
                    </div>
                </header>

                <div
                    class="relative min-h-[350px] lg:min-h-0 flex-grow bg-surface-container-lowest border border-outline-variant/20 rounded-2xl overflow-hidden flex flex-col items-center justify-center shadow-lg group">
                    <div id="ai_result_placeholder"
                        class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 transition-all z-10">
                        <div
                            class="w-16 h-16 rounded-full bg-surface-container-high border border-outline-variant/30 flex items-center justify-center mx-auto mb-4 shadow-inner">
                            <span class="material-symbols-outlined text-3xl text-on-surface-variant">draw</span>
                        </div>
                        <h2 class="font-headline font-black text-xl text-white uppercase mb-2 tracking-widest">
                            Visualisasikan Idenya</h2>
                        <p class="text-xs text-on-surface-variant max-w-sm mx-auto opacity-70 leading-relaxed">Hasil
                            generate AI akan muncul secara utuh di sini.</p>
                    </div>

                    <div id="ai_image_wrapper"
                        class="hidden absolute inset-0 flex flex-col items-center justify-center p-4 md:p-8 z-20 bg-background/80 backdrop-blur-sm">
                        <img id="ai_generated_image" src=""
                            class="w-full h-full object-contain rounded-xl shadow-[0_20px_40px_rgba(0,0,0,0.9)] border border-outline-variant/20 transition-all">
                        <div class="absolute bottom-6 flex gap-4">
                            <button onclick="downloadImg()"
                                class="w-12 h-12 bg-surface-container-high border border-outline-variant/30 text-white rounded-xl flex items-center justify-center hover:text-primary transition-all shadow-xl">
                                <span class="material-symbols-outlined">download</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="h-28 flex-shrink-0 bg-surface-container-lowest border border-outline-variant/20 rounded-2xl p-4 flex flex-col shadow-lg">
                    <div class="flex items-center justify-between px-2 mb-2">
                        <span
                            class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">history</span> Riwayat Desain
                        </span>
                        <a href="{{ route('customer.ai.history') }}"
                            class="text-[10px] font-bold text-primary uppercase tracking-widest hover:text-white transition-colors flex items-center gap-1">
                            Lihat Semua <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                        </a>
                    </div>
                    <div id="design_history_container"
                        class="flex gap-3 overflow-x-auto custom-scrollbar pb-2 items-center flex-grow px-2">
                        <div id="empty_history" class="text-[10px] text-white/30 italic">Memuat riwayat...</div>
                    </div>
                </div>
            </div>

            {{-- KANAN: FORM KONTROL --}}
            <div class="lg:col-span-4 flex flex-col lg:h-full min-h-0 lg:overflow-y-auto custom-scrollbar lg:pr-2 pb-8">
                <div
                    class="bg-surface-container-lowest border border-outline-variant/20 rounded-2xl shadow-2xl relative overflow-hidden flex flex-col h-full">
                    <div class="h-1.5 w-full gold-gradient"></div>

                    <div class="p-6 md:p-8 flex flex-col gap-6 flex-grow">
                        <h2
                            class="font-headline font-black text-xl uppercase tracking-widest text-white border-b border-outline-variant/10 pb-4 flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary">psychology</span>
                            Parameter AI
                        </h2>

                        <div class="flex flex-col flex-grow">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-3 pl-1">
                                Konsep Utama / Deskripsi <span class="text-red-500">*</span>
                            </label>
                            <textarea id="prompt_input" rows="8"
                                placeholder="Ceritakan sedetail mungkin:&#10;&#10;Contoh: Harimau mengaum dengan mahkota emas, gaya maskot esports, detail tinggi, garang, pencahayaan dramatis..."
                                class="w-full flex-grow bg-surface-container-low border border-outline-variant/30 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface font-body p-5 transition-all rounded-xl resize-none shadow-inner text-sm leading-relaxed"></textarea>
                            <div class="flex justify-end mt-1">
                                <span id="char_counter" class="text-[10px] text-on-surface-variant/40">0 / 1000</span>
                            </div>
                        </div>

                        <button id="btn_generate" {{ $creditsLeft <= 0 ? 'disabled' : '' }}
                            class="w-full {{ $creditsLeft <= 0 ? 'bg-surface-container-high text-white/30 cursor-not-allowed' : 'gold-gradient text-on-primary hover:scale-[1.02] shadow-[0_0_20px_rgba(242,202,80,0.2)]' }} py-4 font-headline font-black uppercase text-sm tracking-[0.2em] rounded-xl transition-all flex items-center justify-center gap-2 mt-2">
                            <span class="material-symbols-outlined text-[18px]">
                                {{ $creditsLeft <= 0 ? 'block' : 'magic_button' }}
                            </span>
                            {{ $creditsLeft <= 0 ? 'Kuota Habis' : 'Generate Desain' }}
                        </button>

                        @if ($creditsLeft <= 0)
                            <p class="text-center text-[10px] text-red-400 font-bold uppercase tracking-widest">
                                Kuota Anda sudah mencapai batas maksimal hari ini.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- Variabel Laravel untuk JS — tetap inline karena berisi data dari Blade --}}
    <script>
        window.aiGenerateUrl = "{{ route('customer.ai.generate') }}";
        window.aiOrderUrl = "{{ route('customer.order.create') }}";
        window.aiSessionUrl = "{{ route('customer.ai.session') }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    <script
        src="{{ asset('js/customer/ai/ai-studio.js') }}?v={{ filemtime(public_path('js/customer/ai/ai-studio.js')) }}">
    </script>

</body>

</html>
