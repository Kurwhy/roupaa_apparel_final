@extends('layouts.admin')

@section('title', 'Laporan Keuangan')
@section('header_title', 'Laporan Keuangan')

@push('scripts')
    <script>
        window.laporanApiUrl = "{{ route('ops.owner.api.laporan') }}";
    </script>
    <script src="{{ asset('js/owner/laporan.js') }}"></script>
@endpush

@section('content')
    <div class="p-6 md:p-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-black text-white uppercase tracking-tight">Laporan Keuangan</h1>
                <p class="text-on-surface-variant text-sm" id="label_periode">Ringkasan pendapatan dan pengeluaran ROUPAA
                    Apparel.</p>
            </div>
            <button onclick="printLaporan()"
                class="flex items-center gap-2 bg-primary text-black px-5 py-2.5 rounded-full font-bold text-xs uppercase tracking-widest hover:brightness-110 hover:scale-105 transition-all shadow-[0_0_15px_rgba(242,202,80,0.3)] self-start">
                <span class="material-symbols-outlined text-[16px]">print</span>
                Print Laporan
            </button>
        </div>

        {{-- FILTER --}}
        <div class="flex flex-wrap items-center gap-3">
            <button id="btn_semua" onclick="setFilterSemua()"
                class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest border transition-all bg-primary text-black border-primary">
                Semua
            </button>

            <select id="sel_bulan"
                class="bg-surface-container-low border border-white/10 text-white text-xs rounded-full px-4 py-2 font-bold uppercase tracking-widest focus:outline-none focus:border-primary transition-colors cursor-pointer">
                <option value="">— Pilih Bulan —</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>

            <select id="sel_tahun"
                class="bg-surface-container-low border border-white/10 text-white text-xs rounded-full px-4 py-2 font-bold uppercase tracking-widest focus:outline-none focus:border-primary transition-colors cursor-pointer">
                @foreach ($availableYears as $year)
                    <option value="{{ $year }}" {{ $year === now()->year ? 'selected' : '' }}>{{ $year }}
                    </option>
                @endforeach
            </select>

            <button onclick="applyFilter()"
                class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest border border-white/20 text-white bg-white/5 hover:bg-white/10 transition-all">
                Terapkan
            </button>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface-container-low border border-white/5 p-5 rounded-2xl shadow-lg">
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">Total Pemasukan</p>
                <p class="text-xl font-black text-green-400" id="stat_pendapatan">—</p>
            </div>
            <div class="bg-surface-container-low border border-white/5 p-5 rounded-2xl shadow-lg">
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">Total Pengeluaran
                </p>
                <p class="text-xl font-black text-red-400" id="stat_pengeluaran">—</p>
            </div>
            <div class="bg-surface-container-low border border-white/5 p-5 rounded-2xl shadow-lg">
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">Saldo Bersih</p>
                <p class="text-xl font-black text-primary" id="stat_saldo">—</p>
            </div>
            <div class="bg-surface-container-low border border-white/5 p-5 rounded-2xl shadow-lg">
                <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">Total Transaksi</p>
                <p class="text-xl font-black text-white" id="stat_transaksi">—</p>
            </div>
        </div>

        {{-- TABEL PEMASUKAN --}}
        <div>
            <h2 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-green-400 text-[16px]">trending_up</span>
                Riwayat Pemasukan — Pembayaran Pesanan
            </h2>
            <div class="bg-surface-container-lowest border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/10">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Pesanan
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">
                                    Pelanggan</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Status
                                </th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary text-right">
                                    Jumlah Diterima</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary text-right">
                                    Tanggal</th>
                            </tr>
                        </thead>
                        <tbody id="orders_table" class="divide-y divide-white/5">
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABEL PENGELUARAN --}}
        <div>
            <h2 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-red-400 text-[16px]">trending_down</span>
                Riwayat Pengeluaran — Belanja Bahan Apparel
            </h2>
            <div class="bg-surface-container-lowest border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/10">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">No.
                                    Invoice</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">
                                    Diverifikasi Oleh</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary text-right">
                                    Jumlah Dikeluarkan</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary text-center">
                                    Struk</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary text-right">
                                    Tanggal</th>
                            </tr>
                        </thead>
                        <tbody id="restock_table" class="divide-y divide-white/5">
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL LIGHTBOX STRUK --}}
    <div id="strukModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm cursor-zoom-out" onclick="closeStrukModal()"></div>
        <div class="relative z-10 flex flex-col items-center w-full max-w-2xl max-h-[90vh] p-4">
            <div class="w-full flex justify-end gap-3 mb-4">
                <a id="btn_download_struk" href="#" download
                    class="flex items-center gap-2 bg-primary text-black px-4 py-2 rounded-full font-bold text-xs uppercase tracking-widest hover:brightness-110 hover:scale-105 transition-all shadow-[0_0_15px_rgba(242,202,80,0.5)]">
                    <span class="material-symbols-outlined text-[18px]">download</span> Unduh
                </a>
                <button onclick="closeStrukModal()"
                    class="w-10 h-10 bg-white/10 text-white rounded-full flex items-center justify-center hover:bg-red-500 transition-colors border border-white/20">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div
                class="w-full bg-surface-container-low border border-white/10 rounded-2xl overflow-hidden shadow-2xl flex justify-center">
                <img id="struk_image" src="" alt="Struk Pembelian"
                    class="max-w-full h-auto max-h-[75vh] object-contain rounded-xl scale-95 transition-transform duration-300">
            </div>
        </div>
    </div>
@endsection
