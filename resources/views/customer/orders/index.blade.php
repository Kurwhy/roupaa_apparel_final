<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>ROUPAA | Progress Pesanan</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="{{ asset('js/tailwind-config/customer.js') }}"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    {{-- Proteksi halaman --}}
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
    class="font-body selection:bg-primary selection:text-black bg-background text-on-background min-h-screen flex flex-col">

    @include('components.navbar')

    <main class="flex-grow pt-24 md:pt-32 px-4 md:px-8 pb-24 relative z-10">
        <div class="absolute inset-0 industrial-grid pointer-events-none"></div>

        <div class="max-w-5xl mx-auto relative z-10">
            <header class="text-left border-l-4 border-primary pl-6 mb-12">
                <span class="text-primary font-headline uppercase tracking-[0.2em] text-sm mb-2 block">Dashboard
                    Pelanggan</span>
                <h1
                    class="text-4xl md:text-5xl font-headline font-black uppercase tracking-tighter text-on-background leading-none">
                    Progress Pesanan
                </h1>
            </header>

            <div id="loading-state" class="flex flex-col items-center justify-center py-20">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin mb-4">progress_activity</span>
                <p class="text-on-surface-variant text-sm font-headline tracking-widest uppercase animate-pulse">Menarik
                    Data Pesanan...</p>
            </div>

            <div id="empty-state"
                class="hidden bg-surface-container-low border border-outline-variant/20 p-12 flex-col items-center justify-center text-center rounded shadow-xl">
                <span
                    class="material-symbols-outlined text-6xl text-on-surface-variant opacity-50 mb-4">inventory_2</span>
                <h3 class="font-headline font-bold text-xl uppercase tracking-widest mb-2">Belum Ada Project</h3>
                <p class="text-sm text-on-surface-variant max-w-md mb-8">Anda belum memiliki tiket project yang aktif.
                    Mulai inisiasi pesanan Anda sekarang.</p>
                <a href="{{ route('customer.order.create') }}"
                    class="gold-gradient text-on-primary px-8 py-3 font-headline font-bold uppercase tracking-[0.2em] text-sm rounded shadow-lg shadow-primary/20 hover:scale-105 transition-transform">
                    Buat Order Baru
                </a>
            </div>

            <div id="orders-container" class="space-y-4 hidden"></div>
        </div>
    </main>

    @include('components.footer')

    {{-- URL untuk JS eksternal --}}
    <script>
        window.orderApiUrl = "{{ route('customer.order.api.data') }}";
        window.orderDetailBaseUrl = "/customer/progress-pesanan";
        window.orderCreateUrl = "{{ route('customer.order.create') }}";
    </script>
    <script src="{{ asset('js/customer/orders/orders-index.js') }}"></script>

</body>

</html>
