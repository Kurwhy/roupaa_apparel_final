@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')
@section('header_title', 'Daftar Semua Pesanan')

@push('scripts')
    <script>
        window.ordersApiUrl = "{{ route('ops.api.orders.data') }}";
        window.orderShowUrlBase = "{{ route('ops.orders.show', ['id' => '__ID__']) }}";
    </script>
    <script src="{{ asset('js/admin/orders/index.js') }}"></script>
@endpush

@section('content')
    <div class="relative z-10">
        <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-headline font-black text-white tracking-tight uppercase">Order Management</h1>
                <p class="text-on-surface-variant text-sm mt-1">
                    Total ada <span id="total_count">—</span> project dalam sistem.
                </p>
            </div>

            <div class="relative w-full lg:w-80">
                <span
                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                <input type="text" id="search_input"
                    placeholder="Cari pesanan atau pelanggan..."
                    class="w-full bg-surface-container-low border border-white/10 rounded-full pl-12 pr-5 py-2.5 text-sm text-white placeholder:text-on-surface-variant/40 focus:outline-none focus:border-primary/50 transition-all shadow-inner">
            </div>
        </div>

        <div class="flex overflow-x-auto gap-3 mb-8 pb-2 custom-scrollbar">
            <button id="tab_all" onclick="switchTab('all')"
                class="whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-primary text-on-primary shadow-[0_0_15px_rgba(242,202,80,0.4)]">
                Semua
            </button>
            <button id="tab_diskusi" onclick="switchTab('diskusi')"
                class="whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-surface-container-high border border-white/5 text-on-surface-variant hover:text-white">
                Diskusi
            </button>
            <button id="tab_menunggu_bayar" onclick="switchTab('menunggu_bayar')"
                class="whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-surface-container-high border border-white/5 text-on-surface-variant hover:text-white">
                Menunggu Bayar
            </button>
            <button id="tab_diproses" onclick="switchTab('diproses')"
                class="whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-surface-container-high border border-white/5 text-on-surface-variant hover:text-white">
                Diproses
            </button>
            <button id="tab_selesai" onclick="switchTab('selesai')"
                class="whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-surface-container-high border border-white/5 text-on-surface-variant hover:text-white">
                Siap & Selesai
            </button>
        </div>

        <div class="bg-surface-container-lowest border border-white/5 rounded-3xl shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low border-b border-white/5">
                            <th
                                class="px-8 py-5 text-[10px] uppercase tracking-[0.2em] text-on-surface-variant font-black whitespace-nowrap">
                                Detail Order</th>
                            <th
                                class="px-8 py-5 text-[10px] uppercase tracking-[0.2em] text-on-surface-variant font-black whitespace-nowrap">
                                Pelanggan</th>
                            <th
                                class="px-8 py-5 text-[10px] uppercase tracking-[0.2em] text-on-surface-variant font-black whitespace-nowrap">
                                Project</th>
                            <th
                                class="px-8 py-5 text-[10px] uppercase tracking-[0.2em] text-on-surface-variant font-black whitespace-nowrap">
                                Status</th>
                            <th
                                class="px-8 py-5 text-[10px] uppercase tracking-[0.2em] text-on-surface-variant font-black text-right whitespace-nowrap">
                                Navigasi</th>
                        </tr>
                    </thead>
                    <tbody id="orders_table_body" class="divide-y divide-white/5 font-body">
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <span class="material-symbols-outlined text-3xl text-white/10 animate-spin">sync</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
