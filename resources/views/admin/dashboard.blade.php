@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Ringkasan Sistem')

@push('scripts')
    <script>
        window.ordersIndexUrl = "{{ route('ops.orders.index') }}";
    </script>
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush

@section('content')

    {{-- GREETING --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 relative z-10">
        <div>
            <h1 class="text-3xl font-headline font-black text-white tracking-tight">
                Halo, <span id="user_greeting">Loading...</span>
            </h1>
            <p class="text-on-surface-variant text-sm mt-1">Berikut adalah pantauan aktivitas produksi ROUPAA hari ini.</p>
        </div>
        <div
            class="text-right hidden md:block bg-surface-container-high/50 px-5 py-3 rounded-2xl border border-white/5 shadow-inner">
            <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-1">
                <span id="current_date"></span>
            </p>
            <p class="text-xl font-headline font-black text-primary drop-shadow-[0_0_8px_rgba(242,202,80,0.5)]"
                id="realtime-clock">00:00:00 WIB</p>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5 mb-8 relative z-10">

        <div
            class="bg-surface-container-high border border-white/10 p-5 rounded-2xl hover:border-blue-500/20 transition-colors group">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[18px] text-blue-400">forum</span>
                <span class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold">Diskusi</span>
            </div>
            <h3 class="text-3xl font-headline font-black text-white" id="stat_diskusi">-</h3>
        </div>

        <div
            class="bg-surface-container-high border border-white/10 p-5 rounded-2xl hover:border-yellow-500/20 transition-colors">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[18px] text-yellow-400">payments</span>
                <span class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold">Menunggu Bayar</span>
            </div>
            <h3 class="text-3xl font-headline font-black text-white" id="stat_menunggu_dp">-</h3>
        </div>

        <div class="bg-surface-container-high border border-primary/30 p-5 rounded-2xl">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[18px] text-primary">precision_manufacturing</span>
                <span class="text-[9px] text-primary uppercase tracking-widest font-bold">Produksi</span>
            </div>
            <h3 class="text-3xl font-headline font-black text-primary" id="stat_produksi">-</h3>
        </div>

        <div
            class="bg-surface-container-high border border-white/10 p-5 rounded-2xl hover:border-green-500/20 transition-colors">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[18px] text-green-400">check_circle</span>
                <span class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold">Siap & Selesai</span>
            </div>
            <h3 class="text-3xl font-headline font-black text-white" id="stat_selesai">-</h3>
        </div>

        <div
            class="bg-surface-container-high border border-white/10 p-5 rounded-2xl hover:border-purple-500/20 transition-colors">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[18px] text-purple-400">group</span>
                <span class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold">Customer</span>
            </div>
            <h3 class="text-3xl font-headline font-black text-white" id="stat_customers">-</h3>
        </div>

        <div
            class="bg-surface-container-high border border-white/10 p-5 rounded-2xl hover:border-orange-500/20 transition-colors">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-[18px] text-orange-400">mark_chat_unread</span>
                <span class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold">Chat Baru</span>
            </div>
            <h3 class="text-3xl font-headline font-black text-white" id="stat_unread_chats">-</h3>
        </div>

    </div>

    {{-- PESANAN BARU (belum direspon admin) --}}
    <div id="new_orders_section" class="hidden mb-8 relative z-10">
        <div class="bg-surface-container-low border border-blue-500/20 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-500/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">fiber_new</span>
                    Pesanan Baru — Belum Direspon
                    <span id="new_orders_count"
                        class="bg-blue-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full ml-1">0</span>
                </h3>
            </div>
            <div id="new_orders_list" class="divide-y divide-white/5"></div>
        </div>
    </div>

    {{-- TABEL + SIDEBAR --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 relative z-10">

        {{-- Tabel Pesanan --}}
        <div class="xl:col-span-2 bg-surface-container-low border border-white/10 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">receipt_long</span>
                    Pesanan Terbaru
                </h3>
                <a href="{{ route('ops.orders.index') }}"
                    class="text-[10px] text-primary uppercase tracking-widest font-bold hover:underline">
                    Lihat Semua →
                </a>
            </div>
            <div class="overflow-x-auto">
                <div class="overflow-y-auto max-h-[320px]">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 z-10 bg-surface-container-low">
                            <tr class="border-b border-white/5">
                                <th
                                    class="px-6 py-3 text-[9px] uppercase tracking-widest text-on-surface-variant font-bold whitespace-nowrap">
                                    Order</th>
                                <th
                                    class="px-6 py-3 text-[9px] uppercase tracking-widest text-on-surface-variant font-bold whitespace-nowrap">
                                    Pelanggan</th>
                                <th
                                    class="px-6 py-3 text-[9px] uppercase tracking-widest text-on-surface-variant font-bold whitespace-nowrap">
                                    Project</th>
                                <th
                                    class="px-6 py-3 text-[9px] uppercase tracking-widest text-on-surface-variant font-bold whitespace-nowrap">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-[9px] uppercase tracking-widest text-on-surface-variant font-bold text-right whitespace-nowrap">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="latest_orders_table" class="text-sm divide-y divide-white/5"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Perlu Tindakan --}}
        <div class="bg-surface-container-low border border-white/10 rounded-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-sm font-bold text-white uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-400 text-[18px]">warning</span>
                    Perlu Tindakan
                </h3>
            </div>
            <div id="notifications_panel" class="p-4 overflow-y-auto space-y-3 flex-grow max-h-[500px] custom-scrollbar">
            </div>
        </div>

    </div>

@endsection
