<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>ROUPAA | Riwayat AI Design</title>

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

    <script>
        (function() {
            const token = sessionStorage.getItem('access_token');
            const role = sessionStorage.getItem('user_role');
            if (!token) window.location.href = '/login';
            else if (role !== 'pelanggan') window.location.href = '/';
        })();
    </script>
</head>

<body
    class="font-body selection:bg-primary/30 selection:text-primary bg-background text-on-background min-h-screen flex flex-col relative overflow-x-hidden">

    @include('components.navbar')

    <main class="flex-grow pt-32 pb-16 px-4 md:px-8 relative z-10">
        <div class="absolute inset-0 industrial-grid pointer-events-none opacity-50 z-0"></div>
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-primary/5 blur-[120px] pointer-events-none z-0">
        </div>

        <div class="max-w-7xl mx-auto relative z-10">

            <div
                class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 border-b border-outline-variant/20 pb-6">
                <header class="text-left border-l-[3px] border-primary pl-4 md:pl-6">
                    <span
                        class="text-primary font-headline uppercase tracking-[0.2em] text-xs font-bold mb-2 block flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">collections_bookmark</span> Koleksi Anda
                    </span>
                    <h1
                        class="text-3xl md:text-4xl font-headline font-black uppercase tracking-tight text-white leading-tight">
                        Riwayat AI Design</h1>
                </header>
                <a href="{{ route('customer.ai.index') }}"
                    class="inline-flex w-full md:w-auto justify-center items-center gap-2 px-6 py-3 bg-surface-container-low border border-outline-variant/30 hover:border-primary rounded-xl text-xs font-bold uppercase tracking-widest text-white transition-all">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Studio
                </a>
            </div>

            <div id="loading-state" class="flex flex-col items-center justify-center py-20">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin mb-4">progress_activity</span>
                <p class="text-on-surface-variant text-sm font-headline tracking-widest uppercase animate-pulse">Memuat
                    Koleksi Desain...</p>
            </div>

            <div id="empty-state"
                class="hidden py-20 flex-col items-center justify-center text-center bg-surface-container-lowest border border-outline-variant/20 rounded-3xl border-dashed">
                <span class="material-symbols-outlined text-6xl text-on-surface-variant/30 mb-4">inventory_2</span>
                <h3 class="text-xl font-headline font-bold text-white mb-2 uppercase tracking-widest">Belum Ada Desain
                </h3>
                <p class="text-sm text-on-surface-variant mb-6 max-w-md">Anda belum pernah membuat desain menggunakan
                    AI. Yuk, mulai imajinasikan desain pertamamu sekarang!</p>
                <a href="{{ route('customer.ai.index') }}"
                    class="gold-gradient text-black px-8 py-3 rounded-xl font-headline font-black uppercase text-xs tracking-widest hover:scale-105 transition-transform shadow-lg">
                    Mulai Desain
                </a>
            </div>

            <div id="gallery-container"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 hidden"></div>

        </div>
    </main>

    {{-- Modal Lightbox --}}
    <div id="imageModal"
        class="fixed inset-0 z-[100] bg-black/95 hidden items-center justify-center p-4 md:p-8 opacity-0 transition-opacity duration-300">
        <button onclick="closeModal()"
            class="absolute top-6 right-6 md:top-8 md:right-8 text-on-surface-variant hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full border border-white/20">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <img id="modalImage" src=""
            class="max-w-full max-h-full object-contain rounded-2xl shadow-[0_0_50px_rgba(0,0,0,0.8)] scale-95 transition-transform duration-300">
    </div>

    {{-- URL untuk JS eksternal --}}
    <script>
        window.aiHistoryUrl = "{{ route('customer.ai.history.data') }}";
        window.aiStudioUrl = "{{ route('customer.ai.index') }}";
    </script>
    <script src="{{ asset('js/customer/ai/ai-history.js') }}"></script>

</body>

</html>
