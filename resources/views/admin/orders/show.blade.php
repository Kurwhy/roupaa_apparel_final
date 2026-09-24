@extends('layouts.admin')

@section('title', 'Project: ' . $order->project_name)
@section('header_title', 'Workspace')

@section('content')

    <div class="flex flex-col relative z-10 gap-5 h-full">

        {{-- HEADER & STATUS CARD --}}
        <div
            class="flex-shrink-0 bg-surface-container-lowest border border-white/5 p-5 rounded-3xl shadow-lg relative overflow-hidden">
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-primary/5 rounded-full blur-[80px] pointer-events-none"></div>
            <div class="flex flex-col md:flex-row md:items-center justify-between relative z-10 gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('ops.orders.index') }}"
                        class="w-11 h-11 flex-shrink-0 rounded-full bg-surface border border-white/10 flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-black hover:border-primary transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">arrow_back</span>
                    </a>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-headline font-black text-white uppercase tracking-tight leading-none">
                                {{ $order->project_name }}
                            </h1>
                            <span
                                class="bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest">
                                #{{ $order->order_number }}
                            </span>
                        </div>
                        <span
                            class="text-[11px] text-on-surface-variant flex items-center gap-1.5 mt-1.5 font-medium tracking-wider">
                            <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                            <span id="ui_order_initiated" data-date="{{ $order->created_at->toISOString() }}"></span>
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @php
                        $steps = [
                            'diskusi_desain' => ['icon' => 'design_services', 'label' => 'Desain & Mockup'],
                            'menunggu_spesifikasi' => [
                                'icon' => 'format_list_bulleted',
                                'label' => 'Menunggu Spesifikasi',
                            ],
                            'menunggu_estimasi' => ['icon' => 'calculate', 'label' => 'Estimasi Harga'],
                            'menunggu_pembayaran' => ['icon' => 'payments', 'label' => 'Menunggu Pembayaran'],
                            'diproses' => ['icon' => 'precision_manufacturing', 'label' => 'Proses Produksi'],
                            'siap_diambil' => ['icon' => 'inventory_2', 'label' => 'Siap Diambil/Kirim'],
                            'selesai' => ['icon' => 'check_circle', 'label' => 'Selesai'],
                        ];
                        $currentStatus = $steps[$order->status] ?? [
                            'icon' => 'info',
                            'label' => 'Status Tidak Diketahui',
                        ];
                    @endphp
                    <div id="status_berjalan_badge"
                        class="inline-flex items-center gap-3 bg-surface border border-primary/30 px-4 py-2 rounded-2xl shadow-[0_0_15px_rgba(242,202,80,0.1)]">
                        <div class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px] {{ $order->status == 'diproses' ?: '' }}">
                                {{ $currentStatus['icon'] }}
                            </span>
                        </div>
                        <div class="flex flex-col pr-2">
                            <span class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold">Status
                                Berjalan</span>
                            <span
                                class="text-xs font-black text-white uppercase tracking-wider">{{ $currentStatus['label'] }}</span>
                        </div>
                    </div>
                    <div class="hidden lg:flex items-center gap-3 bg-surface border border-white/5 px-4 py-2 rounded-2xl">
                        <div class="text-right">
                            <p class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold mb-0.5">Pemesan
                            </p>
                            <p class="text-xs font-bold text-white leading-tight">{{ $order->pelanggan->nama_lengkap }}</p>
                        </div>
                        <div
                            class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary flex-shrink-0 border border-primary/20">
                            <span class="material-symbols-outlined text-[18px]">person</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END HEADER --}}

        {{-- MAIN AREA (2 COLUMNS) --}}
        <div
            class="flex flex-col lg:flex-row gap-5 flex-grow min-h-0 overflow-y-auto lg:overflow-hidden custom-scrollbar pb-10 lg:pb-0">

            {{-- LEFT SIDEBAR --}}
            <div
                class="w-full lg:w-4/12 flex flex-col gap-5 lg:overflow-y-auto custom-scrollbar lg:pr-2 lg:pb-2 flex-shrink-0">

                {{-- ACTION PANEL --}}
                <div
                    class="bg-gradient-to-br from-surface-container-low to-surface border border-primary/30 rounded-3xl flex flex-col overflow-hidden shadow-[0_0_30px_rgba(242,202,80,0.05)] relative group flex-shrink-0">
                    <div
                        class="absolute top-0 right-0 w-48 h-48 bg-primary/5 rounded-full blur-[50px] pointer-events-none transition-colors duration-700">
                    </div>
                    <div class="p-4 border-b border-white/5 relative z-10 flex items-center">
                        <h3
                            class="font-headline font-bold text-sm text-primary uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">touch_app</span> Panel Tindakan
                        </h3>
                    </div>
                    <div id="panel_tindakan_admin" class="p-5 relative z-10">
                        @php
                            $mockups = is_array($order->final_mockup_path) ? $order->final_mockup_path : [];
                            $hasMockup = count($mockups) > 0;
                        @endphp

                        @if (!$hasMockup)
                            {{-- STATE 1: MOCKUP BELUM DIUPLOAD --}}
                            <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl mb-4 shadow-inner">
                                <p class="text-xs text-on-surface-variant leading-relaxed">
                                    <span class="text-white font-bold inline-block mb-1">Tugas Anda:</span><br>
                                    Diskusikan desain via Chat hingga fix. Upload mockup final dan tentukan biaya sablon
                                    sebelum dikirim ke customer.
                                </p>
                            </div>
                            <form action="{{ route('ops.orders.mockup.upload', $order->id) }}" method="POST"
                                enctype="multipart/form-data" class="flex flex-col gap-3">
                                @csrf
                                <input type="file" name="mockup_files[]" id="mockup_upload" multiple accept="image/*"
                                    class="hidden">
                                <label for="mockup_upload"
                                    class="w-full cursor-pointer bg-surface border border-white/10 text-on-surface-variant py-4 rounded-2xl font-bold text-[11px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-primary/10 hover:text-primary hover:border-primary/50 transition-colors shadow-inner">
                                    <span class="material-symbols-outlined text-[20px]">add_photo_alternate</span> Pilih
                                    Foto Mockup
                                </label>
                                <div id="image_preview_container" class="hidden flex-wrap gap-2"></div>
                                <div class="border-t border-white/5 pt-4 mt-1 flex flex-col gap-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-widest text-white/50 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[14px] text-primary">sell</span>
                                            Biaya Sablon / pcs
                                        </span>
                                        <span class="text-[9px] text-white/20">belum termasuk harga kain</span>
                                    </div>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-xs font-bold">Rp</span>

                                        <input type="hidden" name="biaya_sablon" id="hidden_biaya_sablon"
                                            value="{{ $order->biaya_sablon > 0 ? (int) $order->biaya_sablon : '' }}">
                                        <input type="text" id="input_biaya_sablon"
                                            value="{{ $order->biaya_sablon > 0 ? number_format((int) $order->biaya_sablon, 0, ',', '.') : '' }}"
                                            placeholder="0" inputmode="numeric"
                                            oninput="
                                                let raw = this.value.replace(/[^0-9]/g, '').slice(0, 5);
                                                document.getElementById('counter_biaya_sablon').textContent = raw.length + '/5';
                                                document.getElementById('hidden_biaya_sablon').value = raw;
                                                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                            "
                                            class="w-full bg-surface border border-primary/30 text-white text-sm font-bold text-right rounded-xl py-3 pl-8 pr-12 focus:border-primary focus:ring-1 focus:ring-primary placeholder:text-white/20 transition-colors">
                                        <span id="counter_biaya_sablon"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">
                                            {{ $order->biaya_sablon > 0 ? strlen((string) (int) $order->biaya_sablon) : '0' }}/5
                                        </span>
                                    </div>
                                    <div
                                        class="bg-surface/50 border border-white/5 rounded-xl p-3 text-[10px] text-white/30 leading-relaxed">
                                        <span
                                            class="material-symbols-outlined text-[12px] align-middle text-primary/40">info</span>
                                        Biaya ini flat untuk semua item. Nanti ditambah harga kain varian yang dipilih
                                        customer × qty.
                                    </div>
                                </div>
                                <button type="submit" id="btn-submit-mockup"
                                    class="w-full bg-primary text-black py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:brightness-110 disabled:opacity-50 transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 mt-1">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                    Kirim Mockup Final + Estimasi
                                </button>
                            </form>
                        @else
                            {{-- STATE 2: MOCKUP SUDAH DIKIRIM --}}
                            <div class="bg-surface-container-high border border-white/10 p-5 rounded-2xl shadow-lg">
                                <div class="flex justify-between items-center mb-3">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary">Mockup Final</p>
                                    @if ($order->status != 'diskusi_desain')
                                        <span
                                            class="bg-green-500/20 text-green-400 text-[9px] px-2 py-1 rounded border border-green-500/30 uppercase tracking-widest font-bold">Disetujui</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    @foreach ($mockups as $mockupPath)
                                        <div class="w-full h-24 rounded-xl overflow-hidden border border-white/20 cursor-zoom-in relative group"
                                            onclick="openImageModal('{{ Storage::url($mockupPath) }}')">
                                            <img src="{{ Storage::url($mockupPath) }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                            <div
                                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <span class="material-symbols-outlined text-white text-2xl">zoom_in</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Ringkasan biaya --}}
                                <div class="flex flex-col gap-2 mb-4">
                                    <div
                                        class="bg-primary/5 border border-primary/10 rounded-xl p-3 flex items-center justify-between">
                                        <span
                                            class="text-[10px] text-white/40 uppercase tracking-widest flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[13px] text-primary">sell</span>
                                            Biaya sablon/pcs
                                        </span>
                                        @if ($order->biaya_sablon > 0)
                                            <span class="text-sm font-black text-primary font-mono">
                                                Rp {{ number_format($order->biaya_sablon, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-amber-400/70 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[12px]">warning</span> Belum
                                                diset
                                            </span>
                                        @endif
                                    </div>

                                    @if ($order->items->count() > 0)
                                        @php
                                            $contohItem = $order->items->first();
                                            $contohHargaKain = (float) ($contohItem->productInventory->harga_jual ?? 0);
                                        @endphp
                                        @if ($contohHargaKain > 0)
                                            <div
                                                class="bg-surface/50 border border-white/5 rounded-xl p-3 flex items-center justify-between">
                                                <span
                                                    class="text-[10px] text-white/40 uppercase tracking-widest flex items-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-[13px] text-white/30">checkroom</span>
                                                    Harga kain/pcs
                                                </span>
                                                <span class="text-xs font-bold text-white/60 font-mono">
                                                    Rp {{ number_format($contohHargaKain, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div
                                                class="bg-primary/10 border border-primary/20 rounded-xl p-3 flex items-center justify-between">
                                                <span
                                                    class="text-[10px] font-bold text-white uppercase tracking-widest flex items-center gap-1.5">
                                                    <span
                                                        class="material-symbols-outlined text-[13px] text-primary">calculate</span>
                                                    Total/pcs
                                                </span>
                                                <span class="text-sm font-black text-primary font-mono">
                                                    Rp
                                                    {{ number_format($contohHargaKain + $order->biaya_sablon, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <div
                                            class="bg-surface/30 border border-dashed border-white/10 rounded-xl p-3 text-center">
                                            <p class="text-[9px] text-white/30 uppercase tracking-widest">
                                                Harga kain menyusul setelah customer pilih varian
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Tombol aksi berdasarkan status --}}
                                @if ($order->status === 'diskusi_desain')
                                    <button type="button"
                                        onclick="document.getElementById('modal_revisi').classList.remove('hidden')"
                                        class="w-full bg-red-500/10 text-red-500 border border-red-500/30 py-3 rounded-xl font-bold text-[11px] uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2 mb-3">
                                        <span class="material-symbols-outlined text-[16px]">replay</span> Revisi Desain
                                        (Hapus)
                                    </button>
                                    <form action="{{ route('ops.orders.lanjutkan', $order->id) }}" method="POST">
                                        @csrf
                                        @if ($order->is_design_approved)
                                            <button type="submit"
                                                class="w-full bg-green-500 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:brightness-110 transition-all shadow-[0_0_20px_rgba(34,197,94,0.4)] flex items-center justify-center gap-2">
                                                <span class="material-symbols-outlined">check_circle</span> Lanjutkan
                                                Pesanan
                                            </button>
                                        @else
                                            <button type="button" disabled
                                                class="w-full bg-surface-container-highest text-on-surface-variant border border-white/10 py-4 rounded-xl font-bold text-xs uppercase tracking-widest opacity-50 cursor-not-allowed flex items-center justify-center gap-2">
                                                <span class="material-symbols-outlined">lock</span> Menunggu ACC Customer
                                            </button>
                                        @endif
                                    </form>
                                @elseif ($order->status === 'menunggu_spesifikasi')
                                    {{-- STATE: MENUNGGU PELANGGAN ISI SPESIFIKASI --}}
                                    <div
                                        class="bg-surface/80 border border-white/5 p-4 rounded-2xl flex items-start gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center flex-shrink-0 border border-blue-500/20">
                                            <span
                                                class="material-symbols-outlined text-blue-400 text-[20px]">hourglass_top</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-blue-400 uppercase tracking-widest">Menunggu
                                                Pelanggan</p>
                                            <p class="text-[10px] text-on-surface-variant mt-1 leading-relaxed">
                                                Pelanggan sedang memilih kombinasi produk dan jumlah pesanan. Anda akan
                                                diberi tahu setelah spesifikasi disimpan.
                                            </p>
                                        </div>
                                    </div>
                                @elseif ($order->status === 'menunggu_estimasi')
                                    {{-- STATE: SPESIFIKASI MASUK, MENUNGGU KONFIRMASI ADMIN --}}
                                    <div class="space-y-3">
                                        <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl">
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[14px]">list_alt</span> Rincian
                                                Pesanan
                                            </p>
                                            @foreach ($order->items as $item)
                                                <div
                                                    class="flex items-start justify-between py-2 border-b border-white/5 last:border-0">
                                                    <div>
                                                        <p class="text-[11px] font-bold text-white">
                                                            {{ $item->productInventory->variant_name ?? '-' }}</p>
                                                        <p class="text-[9px] text-on-surface-variant">
                                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}/pcs ×
                                                            {{ $item->qty }}
                                                        </p>
                                                    </div>
                                                    <p class="text-xs font-black text-primary ml-2">Rp
                                                        {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                                </div>
                                            @endforeach
                                            <div
                                                class="flex items-center justify-between mt-3 pt-2 border-t border-white/10">
                                                <span class="text-[10px] font-bold text-white uppercase">Total Harga</span>
                                                <span class="text-sm font-black text-primary">Rp
                                                    {{ number_format($order->final_price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-surface/50 border border-white/5 rounded-xl p-3 text-[10px] text-white/40 leading-relaxed">
                                            <span
                                                class="material-symbols-outlined text-[12px] align-middle text-primary/40">info</span>
                                            Harga dihitung otomatis oleh sistem dari harga kain + biaya sablon yang sudah
                                            Anda set sebelumnya. Cek kembali rincian di atas, lalu konfirmasi agar pelanggan
                                            bisa lanjut ke pembayaran.
                                        </div>

                                        <form action="{{ route('ops.orders.set-harga', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="w-full bg-green-500 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:brightness-110 transition-all shadow-[0_0_20px_rgba(34,197,94,0.4)] flex items-center justify-center gap-2">
                                                <span class="material-symbols-outlined">check_circle</span> Konfirmasi &
                                                Lanjut Pembayaran
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($order->status === 'diproses')
                                    {{-- STATE: SEDANG DIPROSES --}}
                                    <div class="space-y-3">
                                        {{-- Info Pembayaran --}}
                                        <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl">
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[14px]">payments</span>
                                                Pembayaran
                                            </p>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[10px] text-on-surface-variant">Status</span>
                                                @if ($order->payment_status === 'paid')
                                                    <span
                                                        class="bg-green-500/20 text-green-400 border border-green-500/30 px-2 py-0.5 rounded text-[9px] font-bold uppercase">Lunas</span>
                                                @elseif ($order->payment_status === 'dp_paid')
                                                    <span
                                                        class="bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 px-2 py-0.5 rounded text-[9px] font-bold uppercase">DP
                                                        Lunas</span>
                                                @else
                                                    <span
                                                        class="bg-red-500/20 text-red-400 border border-red-500/30 px-2 py-0.5 rounded text-[9px] font-bold uppercase">Belum
                                                        Bayar</span>
                                                @endif
                                            </div>
                                            @if ($order->payment_type === 'dp')
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-[10px] text-on-surface-variant">DP Dibayar</span>
                                                    <span class="text-xs font-bold text-primary">Rp
                                                        {{ number_format($order->dp_amount, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[10px] text-on-surface-variant">Sisa Lunas</span>
                                                    <span class="text-xs font-bold text-yellow-400">
                                                        Rp
                                                        {{ number_format($order->final_price - $order->dp_amount, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Detail Pesanan --}}
                                        <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl">
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[14px]">list_alt</span> Detail
                                                Pesanan
                                            </p>
                                            @foreach ($order->items as $item)
                                                <div
                                                    class="flex items-start justify-between py-2 border-b border-white/5 last:border-0">
                                                    <div>
                                                        <p class="text-[11px] font-bold text-white">
                                                            {{ $item->productInventory->variant_name ?? '-' }}</p>
                                                        <p class="text-[9px] text-on-surface-variant">
                                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}/pcs ×
                                                            {{ $item->qty }}
                                                        </p>
                                                    </div>
                                                    <p class="text-xs font-black text-primary ml-2">Rp
                                                        {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                                </div>
                                            @endforeach
                                            <div
                                                class="flex items-center justify-between mt-3 pt-2 border-t border-white/10">
                                                <span class="text-[10px] font-bold text-white uppercase">Total</span>
                                                <span class="text-sm font-black text-primary">Rp
                                                    {{ number_format($order->final_price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        {{-- Status info DP --}}
                                        @if ($order->payment_status === 'dp_paid')
                                            <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-3">
                                                <p
                                                    class="text-[10px] text-blue-400 font-bold uppercase tracking-widest flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[14px]">info</span>
                                                    Info
                                                </p>
                                                <p class="text-[9px] text-on-surface-variant mt-1 leading-relaxed">
                                                    Produksi bisa langsung dimulai. Pelunasan sisa pembayaran bisa dilakukan
                                                    customer kapan saja sebelum pengiriman/pengambilan.
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Form Tandai Selesai Produksi --}}
                                        <div class="bg-surface/80 border border-primary/20 p-4 rounded-2xl">
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[14px]">photo_camera</span>
                                                Selesaikan Produksi
                                            </p>
                                            <p class="text-[9px] text-on-surface-variant mb-3 leading-relaxed">
                                                Upload foto hasil produksi sebagai bukti, lalu tandai pesanan selesai
                                                diproduksi.
                                            </p>
                                            <form action="{{ route('ops.orders.mark-complete', $order->id) }}"
                                                method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                                                @csrf
                                                <input type="file" name="production_photos[]"
                                                    id="production_photo_upload" multiple accept="image/*"
                                                    class="hidden">
                                                <label for="production_photo_upload"
                                                    class="w-full cursor-pointer bg-surface border border-white/10 text-on-surface-variant py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-primary/10 hover:text-primary hover:border-primary/50 transition-colors shadow-inner">
                                                    <span
                                                        class="material-symbols-outlined text-[18px]">add_photo_alternate</span>
                                                    Pilih Foto Hasil Produksi
                                                </label>
                                                <div id="production_preview_container" class="hidden flex-wrap gap-2">
                                                </div>
                                                <textarea name="production_notes" rows="2" placeholder="Catatan produksi (opsional)..."
                                                    class="w-full bg-surface border border-white/10 text-white text-xs rounded-xl px-3 py-2 focus:border-primary resize-none"></textarea>
                                                <button type="submit" id="btn_mark_complete" disabled
                                                    class="w-full bg-green-500 text-white py-3.5 rounded-xl font-black text-xs uppercase tracking-widest hover:brightness-110 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-lg flex items-center justify-center gap-2">
                                                    <span class="material-symbols-outlined text-[16px]">verified</span>
                                                    Tandai Selesai Produksi
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @elseif ($order->status === 'siap_diambil')
                                    {{-- STATE: SIAP DIAMBIL / DIKIRIM --}}
                                    <div class="space-y-3">
                                        <div
                                            class="bg-green-500/5 border border-green-500/20 rounded-xl p-4 flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center flex-shrink-0 border border-green-500/30">
                                                <span
                                                    class="material-symbols-outlined text-green-400 text-[20px]">inventory_2</span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-green-400 uppercase tracking-widest">
                                                    Produksi Selesai</p>
                                                <p class="text-[10px] text-on-surface-variant mt-1 leading-relaxed">
                                                    Pesanan sudah selesai diproduksi dan menunggu pengambilan/pengiriman.
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Foto Hasil --}}
                                        @php
                                            $prodPhotos = is_array($order->production_photo_path)
                                                ? $order->production_photo_path
                                                : [];
                                        @endphp
                                        @if (count($prodPhotos) > 0)
                                            <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl">
                                                <p
                                                    class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3">
                                                    Foto Hasil Produksi</p>
                                                <div class="grid grid-cols-2 gap-2">
                                                    @foreach ($prodPhotos as $photo)
                                                        <div class="h-24 rounded-xl overflow-hidden border border-white/10 cursor-zoom-in"
                                                            onclick="openImageModal('{{ Storage::url($photo) }}')">
                                                            <img src="{{ Storage::url($photo) }}"
                                                                class="w-full h-full object-cover hover:scale-105 transition-transform">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Info Pembayaran --}}
                                        <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[10px] text-on-surface-variant">Pembayaran</span>
                                                @if ($order->payment_status === 'paid')
                                                    <span
                                                        class="bg-green-500/20 text-green-400 border border-green-500/30 px-2 py-0.5 rounded text-[9px] font-bold uppercase">Lunas</span>
                                                @else
                                                    <span
                                                        class="bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 px-2 py-0.5 rounded text-[9px] font-bold uppercase">Belum
                                                        Lunas — Sisa Rp
                                                        {{ number_format($order->final_price - $order->dp_amount, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Tombol Selesai --}}
                                        @if ($order->payment_status === 'paid')
                                            <form action="{{ route('ops.orders.mark-delivered', $order->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="w-full bg-primary text-black py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:brightness-110 transition-all shadow-[0_0_20px_rgba(242,202,80,0.4)] flex items-center justify-center gap-2">
                                                    <span class="material-symbols-outlined">check_circle</span>
                                                    Tandai Pesanan Selesai
                                                </button>
                                            </form>
                                        @else
                                            <div
                                                class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-3 text-center">
                                                <p class="text-[10px] text-yellow-400 font-bold uppercase tracking-widest">
                                                    Menunggu Pelunasan</p>
                                                <p class="text-[9px] text-on-surface-variant mt-1">Customer harus melunasi
                                                    sisa pembayaran sebelum pesanan bisa diselesaikan.</p>
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($order->status === 'selesai')
                                    {{-- STATE: SELESAI --}}
                                    <div class="space-y-3">
                                        <div
                                            class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center flex-shrink-0 border border-primary/30">
                                                <span
                                                    class="material-symbols-outlined text-primary text-[20px]">verified</span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-primary uppercase tracking-widest">Pesanan
                                                    Selesai</p>
                                                <p class="text-[10px] text-on-surface-variant mt-1">
                                                    Diselesaikan pada
                                                    {{ $order->completed_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                                </p>
                                            </div>
                                        </div>

                                        @php $prodPhotos = is_array($order->production_photo_path) ? $order->production_photo_path : []; @endphp
                                        @if (count($prodPhotos) > 0)
                                            <div class="bg-surface/80 border border-white/5 p-4 rounded-2xl">
                                                <p
                                                    class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3">
                                                    Foto Hasil</p>
                                                <div class="grid grid-cols-2 gap-2">
                                                    @foreach ($prodPhotos as $photo)
                                                        <div class="h-24 rounded-xl overflow-hidden border border-white/10 cursor-zoom-in"
                                                            onclick="openImageModal('{{ Storage::url($photo) }}')">
                                                            <img src="{{ Storage::url($photo) }}"
                                                                class="w-full h-full object-cover hover:scale-105 transition-transform">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Status lain --}}
                                    <button type="button"
                                        onclick="document.getElementById('modal_revisi').classList.remove('hidden')"
                                        class="w-full mt-2 bg-surface border border-red-500/30 text-red-400 py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[14px]">warning</span> Batal ACC &
                                        Revisi Ulang
                                    </button>
                                @endif

                            </div>
                        @endif
                    </div>
                </div>
                {{-- END ACTION PANEL --}}

                {{-- BRIEF DATA PANEL --}}
                <div
                    class="bg-surface-container-lowest border border-white/5 rounded-3xl flex flex-col flex-shrink-0 overflow-hidden mb-2">
                    <div class="p-4 border-b border-white/5 bg-surface/30">
                        <h3
                            class="font-headline font-bold text-xs text-white uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[18px]">inventory_2</span> Brief
                            Pesanan
                        </h3>
                    </div>
                    <div class="p-5 space-y-6">
                        <div>
                            <p
                                class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">edit_note</span> Catatan Pelanggan
                            </p>
                            <div
                                class="text-xs text-white/90 bg-surface/50 border border-white/5 p-4 rounded-2xl leading-relaxed shadow-inner">
                                {{ $order->design_notes ?? 'Tidak ada catatan spesifik.' }}
                            </div>
                        </div>
                        <div>
                            <p
                                class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">photo_library</span> File Referensi
                            </p>
                            @if ($order->reference_file_path)
                                @php
                                    $ext = strtolower(pathinfo($order->reference_file_path, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                @endphp
                                @if ($isImg)
                                    <div onclick="openImageModal('{{ asset('storage/' . $order->reference_file_path) }}')"
                                        class="block group relative overflow-hidden rounded-2xl border border-white/10 shadow-md cursor-zoom-in">
                                        <img src="{{ asset('storage/' . $order->reference_file_path) }}"
                                            class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div
                                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                                            <span class="material-symbols-outlined text-white text-2xl">zoom_in</span>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ asset('storage/' . $order->reference_file_path) }}" target="_blank"
                                        class="bg-surface border border-white/10 rounded-2xl p-3 flex items-center gap-3 hover:border-primary/50 transition-colors">
                                        <div
                                            class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                                            <span class="material-symbols-outlined text-[20px]">draft</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-white uppercase">File .{{ $ext }}
                                            </p>
                                            <span class="text-[10px] text-primary">Buka File</span>
                                        </div>
                                    </a>
                                @endif
                            @else
                                <div
                                    class="bg-surface/30 border border-dashed border-white/10 rounded-2xl p-4 text-center">
                                    <p class="text-[10px] text-on-surface-variant/50 uppercase tracking-widest">Tidak ada
                                        file</p>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p
                                class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">straighten</span> Rincian Ukuran
                            </p>
                            @if ($order->items && $order->items->count() > 0)
                                <ul class="space-y-2">
                                    @foreach ($order->items as $item)
                                        <li
                                            class="flex items-center gap-3 p-3 bg-surface/50 rounded-xl border border-white/5">
                                            <div
                                                class="bg-primary/10 text-primary border border-primary/20 rounded-lg w-10 h-10 flex items-center justify-center flex-shrink-0">
                                                <span class="font-black text-sm">{{ $item->qty }}</span>
                                            </div>
                                            <div class="flex flex-col text-[11px] text-white/80 leading-snug">
                                                <span>{{ $item->productInventory->variant_name ?? '-' }}</span>
                                                @if ($order->biaya_sablon > 0)
                                                    <span class="text-[9px] text-primary/60 font-mono mt-0.5">
                                                        Rp
                                                        {{ number_format(($item->productInventory->harga_jual ?? 0) + $order->biaya_sablon, 0, ',', '.') }}/pcs
                                                    </span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div
                                    class="bg-surface/30 border border-dashed border-white/10 rounded-2xl p-4 text-center">
                                    <p class="text-[10px] text-on-surface-variant/50 uppercase tracking-widest">Belum Diisi
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- END BRIEF DATA PANEL --}}

            </div>
            {{-- END LEFT SIDEBAR --}}

            {{-- RIGHT MAIN AREA: CHAT --}}
            <div
                class="w-full lg:w-8/12 bg-surface-container-highest border border-white/10 rounded-3xl flex flex-col h-[70vh] lg:h-full overflow-hidden shadow-2xl relative flex-shrink-0">
                <div
                    class="p-5 border-b border-white/5 bg-surface/80 backdrop-blur-xl flex items-center justify-between relative z-20 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                            <span class="material-symbols-outlined text-[20px]">forum</span>
                        </div>
                        <div>
                            <h3 class="font-headline font-bold text-sm text-white uppercase tracking-widest">Ruang Diskusi
                            </h3>
                            <p class="text-[10px] text-on-surface-variant mt-0.5">Komunikasi langsung dengan pelanggan</p>
                        </div>
                    </div>
                </div>

                <div id="chat_area"
                    class="flex-grow p-6 overflow-y-auto custom-scrollbar flex flex-col gap-6 z-10 bg-surface-container-lowest">
                </div>
                <div class="p-4 bg-surface/90 backdrop-blur-md border-t border-white/10 relative z-20 flex flex-col">
                    <div id="chat_file_preview_container" class="hidden mb-3 ml-14 relative inline-block animate-fade-in">
                        <div
                            class="w-16 h-16 rounded-xl overflow-hidden border border-white/20 bg-surface-container-highest shadow-md">
                            <img id="chat_file_preview_image" src=""
                                class="w-full h-full object-cover hidden cursor-zoom-in hover:brightness-75 transition-all"
                                onclick="if(this.src) openImageModal(this.src)" title="Klik untuk perbesar">
                            <div id="chat_file_preview_doc"
                                class="w-full h-full flex flex-col items-center justify-center text-primary bg-primary/10 hidden">
                                <span class="material-symbols-outlined text-[24px]">draft</span>
                            </div>
                        </div>
                        <button type="button" id="btn_remove_chat_file"
                            class="absolute -top-2 -right-2 w-6 h-6 bg-surface-container-high border border-white/20 text-on-surface-variant rounded-full flex items-center justify-center hover:text-white hover:bg-red-500 hover:border-red-500 transition-all shadow-lg z-10"
                            title="Hapus Lampiran">
                            <span class="material-symbols-outlined text-[12px]">close</span>
                        </button>
                    </div>
                    <form id="chat_form" action="{{ route('ops.orders.chat.store', $order->id) }}" method="POST"
                        enctype="multipart/form-data" class="flex items-end gap-3">
                        @csrf
                        <label
                            class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-2xl bg-surface-container-highest border border-white/40 text-on-surface-variant hover:text-white hover:border-white cursor-pointer transition-all shadow-inner group/attach">
                            <span
                                class="material-symbols-outlined text-[22px] group-hover/attach:scale-110 transition-transform">attach_file</span>
                            <input type="file" name="attachment_file" id="chat_file_input" class="hidden"
                                accept="image/*">
                        </label>
                        <div class="flex-grow relative">
                            <textarea name="message" rows="1" placeholder="Ketik pesan untuk pelanggan..."
                                class="w-full bg-surface-container-lowest border border-white/40 hover:border-white/60 rounded-2xl pl-4 pr-12 py-3 text-sm leading-[22px] text-white focus:border-white focus:ring-1 focus:ring-white resize-none min-h-[48px] max-h-[120px] custom-scrollbar shadow-inner block transition-colors"></textarea>
                        </div>
                        <button type="submit"
                            class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-2xl bg-primary text-black hover:brightness-110 transition-all shadow-[0_0_20px_rgba(242,202,80,0.3)] hover:scale-105 active:scale-95">
                            <span class="material-symbols-outlined text-[20px] ml-1">send</span>
                        </button>
                    </form>
                </div>
            </div>
            {{-- END CHAT --}}

        </div>
        {{-- END MAIN AREA --}}

    </div>
    {{-- END outer flex-col --}}

    {{-- MODAL LIGHTBOX --}}
    <div id="image_modal"
        class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm cursor-zoom-out" onclick="closeImageModal()"></div>
        <div class="relative z-10 flex flex-col items-center max-w-[90vw] max-h-[90vh]">
            <div class="w-full flex justify-end gap-3 mb-4">
                <a id="btn_download_modal" href="#" download
                    class="flex items-center gap-2 bg-primary text-black px-4 py-2 rounded-full font-bold text-xs uppercase tracking-widest hover:brightness-110 hover:scale-105 transition-all shadow-[0_0_15px_rgba(242,202,80,0.5)]">
                    <span class="material-symbols-outlined text-[18px]">download</span> Unduh
                </a>
                <button onclick="closeImageModal()"
                    class="w-10 h-10 bg-white/10 text-white rounded-full flex items-center justify-center hover:bg-red-500 transition-colors border border-white/20">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <img id="modal_image_content" src=""
                class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-2xl scale-95 transition-transform duration-300">
        </div>
    </div>

    {{-- MODAL KONFIRMASI REVISI --}}
    <div id="modal_revisi"
        class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="bg-surface-container-high border border-white/10 rounded-3xl p-6 max-w-sm w-full shadow-2xl transform scale-100 transition-all text-center">
            <div
                class="w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center mx-auto mb-4 border border-red-500/30">
                <span class="material-symbols-outlined text-3xl">delete_sweep</span>
            </div>
            <h3 class="text-white font-headline font-bold text-lg mb-2">Revisi Desain?</h3>
            <p class="text-sm text-on-surface-variant mb-6 leading-relaxed">Tindakan ini akan menghapus foto mockup yang
                ada dan mengembalikan status pesanan ke <strong class="text-primary">Diskusi Desain</strong>.</p>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modal_revisi').classList.add('hidden')"
                    class="flex-1 py-3 rounded-xl border border-white/10 text-white font-bold text-xs uppercase tracking-widest hover:bg-white/5 transition-all">Batal</button>
                <form action="{{ route('ops.orders.mockup.revisi', $order->id) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-red-500 text-white font-bold text-xs uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg">Ya,
                        Hapus</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.APP_CONFIG = {
            orderId: {{ $order->id }},
            csrfToken: "{{ csrf_token() }}",
            apiToken: sessionStorage.getItem('access_token'),
            markReadUrl: "{{ route('ops.orders.chat.read', $order->id) }}"
        };
        window.APP_CONFIG.currentUserId = (function() {
            if (!window.APP_CONFIG.apiToken) return 0;
            try {
                const payload = JSON.parse(atob(window.APP_CONFIG.apiToken.split('.')[1]));
                return payload.sub || 0;
            } catch (e) {
                return 0;
            }
        })();
        window.INITIAL_CHATS = {!! json_encode($initialChats) !!};
    </script>
    <script>
        window.ORDER_ID = {{ $order->id }};
    </script>
    <script
        src="{{ asset('js/admin/orders/admin-order.js') }}?v={{ filemtime(public_path('js/admin/orders/admin-order.js')) }}">
    </script>
@endpush
