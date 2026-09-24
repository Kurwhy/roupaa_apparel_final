@extends('layouts.admin')

@section('title', 'Manajemen Inventori')
@section('header_title', 'Manajemen Inventori')

@section('content')
    <div class="absolute inset-0 p-4 md:p-8 flex flex-col overflow-hidden">

        {{-- HEADER PAGE --}}
        <div class="mb-6 flex-shrink-0">
            <h1 class="text-2xl font-headline font-black uppercase text-white tracking-tighter flex items-center gap-3">
                <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                Stok Barang Fisik
            </h1>
            <p class="text-on-surface-variant text-xs pl-4 opacity-70 mt-1">Pantau ketersediaan kaos, hoodie, dan produk
                lainnya di gudang secara real-time.</p>
        </div>

        <div
            class="flex flex-col xl:flex-row xl:items-center justify-between border-b border-white/5 pb-4 flex-shrink-0 mb-4 gap-4 xl:gap-0">
            <div class="flex flex-nowrap items-center gap-2 overflow-x-auto custom-scrollbar pb-2 xl:pb-0">
                <div class="relative flex-shrink-0 w-48 sm:w-64">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-[18px]">search</span>
                    <input type="text" id="search_input" placeholder="Cari Barang..."
                        class="w-full bg-surface-container-low border border-white/10 text-white text-xs rounded-lg pl-9 pr-4 py-2 focus:border-primary placeholder:text-white/20 transition-all">
                </div>

                <button onclick="openAddModal()"
                    class="bg-primary text-black px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:brightness-110 transition-colors shadow-[0_0_15px_rgba(242,202,80,0.2)] flex items-center gap-2 flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah Barang
                </button>

                <button onclick="openReceiveModal()"
                    class="bg-primary/20 text-primary border border-primary/50 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-primary hover:text-black transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">inventory</span> Verifikasi Barang Datang
                </button>

                <button onclick="openRestockModal()"
                    class="bg-white text-black px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest hover:brightness-90 transition-colors flex items-center gap-2 flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">print</span> Buat Daftar Belanja
                </button>
            </div>
        </div>

        {{-- KONTEN STOK APPAREL --}}
        <div class="flex-grow overflow-y-auto custom-scrollbar pb-10 pr-2">

            @php
                $groupedApparel = $inventories->groupBy('product_id');
            @endphp

            <div id="inventory_grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">

                @foreach ($groupedApparel as $productId => $items)
                    @php
                        // 1. Definisikan Produk Induk & Teks Pencarian
                        $product = $items->first()->product;
                        $searchableText = strtolower(
                            $product->name .
                                ' ' .
                                $product->category->name .
                                ' ' .
                                $items->pluck('variant_name')->implode(' ') .
                                ' ' .
                                $items->pluck('sku')->implode(' '),
                        );

                        // 2. Ambil nama kategori dan produk untuk logika gambar otomatis
                        $kategori = strtolower($product->category->name ?? '');
                        $namaProduk = strtolower($product->name ?? '');
                        $teksGabungan = $kategori . ' ' . $namaProduk;

                        // 3. Set foto default
                        $fileName = 'no-image.png';

                        // 4. Logika pendeteksian gambar otomatis berdasarkan 7 file lo
                        if (str_contains($teksGabungan, 'bendera')) {
                            $fileName = 'bendera.jpg';
                        } elseif (str_contains($teksGabungan, 'jersey')) {
                            $fileName = 'jersey.jpg';
                        } elseif (str_contains($teksGabungan, 'kaos')) {
                            $fileName = 'kaos.jpg';
                        } elseif (str_contains($teksGabungan, 'kemeja')) {
                            $fileName = 'kemeja.jpg';
                        } elseif (str_contains($teksGabungan, 'pdl')) {
                            $fileName = 'pdl.jpg';
                        } elseif (str_contains($teksGabungan, 'polo')) {
                            $fileName = 'polo.jpg';
                        } elseif (str_contains($teksGabungan, 'topi')) {
                            $fileName = 'topi.jpg';
                        } elseif (str_contains($teksGabungan, 'stiker')) {
                            $fileName = 'stiker.jpg';
                        }

                        // 5. Arahkan ke folder public/img/
                        $imgSrc = asset('img/' . $fileName);
                    @endphp

                    <div class="inventory-card bg-surface-container-low border border-white/5 rounded-2xl flex flex-col shadow-lg hover:border-white/10 transition-colors overflow-hidden h-[420px]"
                        data-search="{{ $searchableText }}">

                        <div
                            class="p-5 flex items-start gap-4 border-b border-white/5 bg-surface-container-highest/30 flex-shrink-0">
                            <img src="{{ $imgSrc }}"
                                class="w-16 h-16 rounded-xl object-cover border border-white/10 shadow-md">
                            <div class="flex flex-col flex-grow">
                                <span
                                    class="text-[9px] font-bold text-primary uppercase tracking-widest mb-1">{{ $product->category->name }}</span>
                                <h3 class="font-bold text-white text-sm leading-tight mb-2">{{ $product->name }}</h3>
                                <div
                                    class="flex items-center gap-2 text-[10px] text-on-surface-variant bg-black/20 w-fit px-2 py-1 rounded-md border border-white/5">
                                    <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                                    Total: <strong class="text-white">{{ $items->sum('stock') }} Pcs</strong>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-5 py-2 flex justify-between items-center bg-black/10 text-[9px] font-bold uppercase tracking-widest text-white/40 flex-shrink-0">
                            <span>Varian (Warna & Ukuran)</span>
                            <span class="flex gap-6 pr-9">
                                <span>Harga Jual</span>
                                <span>Stok</span>
                            </span>
                        </div>

                        <div class="flex-grow overflow-y-auto custom-scrollbar p-2">
                            <div class="flex flex-col gap-1">
                                @foreach ($items as $inv)
                                    @php
                                        $parts = explode(' - ', $inv->variant_name);
                                        $shortVariant =
                                            count($parts) > 1
                                                ? implode(' - ', array_slice($parts, 1))
                                                : $inv->variant_name;
                                    @endphp

                                    <div
                                        class="flex items-center justify-between p-3 rounded-xl hover:bg-white/5 transition-colors group border border-transparent hover:border-white/5">
                                        <div class="flex flex-col pr-2 overflow-hidden flex-grow">
                                            <span class="text-xs font-medium text-white truncate"
                                                title="{{ $shortVariant }}">{{ $shortVariant }}</span>
                                            <span
                                                class="text-[9px] text-on-surface-variant font-mono mt-0.5">{{ $inv->sku }}</span>
                                        </div>

                                        <div class="flex items-center gap-3 flex-shrink-0">
                                            <div class="text-right">
                                                @if ($inv->harga_jual > 0)
                                                    <span class="text-[11px] font-bold text-green-400 font-mono">
                                                        Rp {{ number_format($inv->harga_jual, 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="text-[10px] font-bold text-amber-500/80 flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[12px]">warning</span>
                                                        Belum diset
                                                    </span>
                                                @endif
                                            </div>

                                            <span
                                                class="text-sm font-black {{ $inv->stock < 20 ? 'text-red-500' : 'text-primary' }} bg-black/30 px-3 py-1 rounded-lg border border-white/5 min-w-[3rem] text-center">
                                                {{ $inv->stock }}
                                            </span>

                                            <button
                                                onclick="openEditModal( '{{ $inv->id }}', 'apparel', '{{ addslashes($shortVariant) }}', '{{ $inv->stock }}', 'Pcs', {{ (float) ($inv->harga_jual ?? 0) }} )"
                                                class="w-7 h-7 rounded-lg bg-white/5 text-white/30 hover:bg-primary hover:text-black hover:border-primary border border-white/10 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100"
                                                title="Edit Stok & Harga">
                                                <span class="material-symbols-outlined text-[14px]">edit</span>
                                            </button>
                                            <button
                                                onclick="openDeleteModal('{{ $inv->id }}', '{{ addslashes($shortVariant) }}')"
                                                class="w-7 h-7 rounded-lg bg-white/5 text-white/30 hover:bg-red-500 hover:text-white hover:border-red-500 border border-white/10 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100"
                                                title="Hapus Varian">
                                                <span class="material-symbols-outlined text-[14px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- EMPTY STATE PENCARIAN --}}
                <div id="inventory_empty_state" class="hidden col-span-full py-20 text-center">
                    <div class="flex flex-col items-center opacity-30">
                        <span class="material-symbols-outlined text-6xl mb-4">search_off</span>
                        <p class="font-headline font-bold uppercase tracking-widest text-white">Barang Tidak Ditemukan</p>
                        <p class="text-xs text-on-surface-variant mt-1">Coba kata kunci lain.</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- JEMBATAN VARIABEL PHP KE JAVASCRIPT --}}
    <script>
        window.lowStockItems = [
            @foreach ($inventories->where('stock', '<', 20) as $inv)
                {
                    id: "{{ $inv->id }}",
                    type: "apparel",
                    name: {!! json_encode($inv->product->name . ' (' . $inv->variant_name . ')') !!},
                    stock: "{{ $inv->stock }}",
                    unit: "Pcs"
                },
            @endforeach
        ];

        window.allItems = [
            @foreach ($inventories as $inv)
                {
                    id: "{{ $inv->id }}",
                    type: "apparel",
                    name: {!! json_encode($inv->product->name . ' (' . $inv->variant_name . ')') !!},
                    stock: "{{ $inv->stock }}",
                    unit: "Pcs"
                },
            @endforeach
        ];
    </script>

@endsection

@push('modals')

    {{-- MODAL DAFTAR BELANJA / VERIFIKASI BARANG --}}
    <div id="restockModal"
        class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
        <div
            class="bg-surface-container-low border border-white/10 rounded-2xl w-full max-w-4xl shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-surface-container-highest/30">
                <div>
                    <h2 class="text-xl font-headline font-black text-white uppercase tracking-widest">Daftar Pengajuan
                        Belanja</h2>
                    <p class="text-xs text-on-surface-variant mt-1">Sistem otomatis memasukkan barang dengan stok rendah.
                        Anda bisa menyesuaikan jumlahnya.</p>
                </div>
                <button onclick="closeRestockModal()" class="text-white/50 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </div>

            <form action="{{ route('ops.inventory.print-restock') }}" method="POST" target="_blank"
                class="flex-grow overflow-hidden flex flex-col">
                @csrf
                <div class="flex-grow overflow-y-auto p-4 md:p-6">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[600px] text-left text-sm text-on-surface-variant">
                            <thead class="uppercase tracking-widest text-primary text-[10px] border-b border-white/10">
                                <tr>
                                    <th class="pb-3 w-1/2">Nama Barang</th>
                                    <th class="pb-3 w-1/6 text-center">Stok Saat Ini</th>
                                    <th class="pb-3 w-1/4 text-center">Jumlah Beli</th>
                                    <th class="pb-3 w-1/12 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="restock-table-body" class="divide-y divide-white/5">
                                {{-- Baris JS --}}
                            </tbody>
                        </table>

                        <button type="button" onclick="addManualRow()"
                            class="mt-4 w-full py-3 border border-dashed border-white/20 rounded-xl text-xs font-bold uppercase tracking-widest text-white/50 hover:text-white hover:border-white/50 hover:bg-white/5 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">add_circle</span> Tambah Barang Manual
                        </button>
                    </div>

                    <div id="pengeluaran_section" class="hidden mt-6 pt-6 border-t border-white/10">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">receipt_long</span> Catat Pengeluaran
                            Belanja
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block">Total
                                    Uang Dikeluarkan (Rp) *</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-white/30 text-sm font-bold">Rp</span>

                                    <input type="hidden" name="total_pengeluaran" id="hidden_total_pengeluaran"
                                        value="{{ isset($order->total_pengeluaran) && $order->total_pengeluaran > 0 ? (int) $order->total_pengeluaran : '' }}">

                                    <input type="text" id="input_total_pengeluaran"
                                        value="{{ isset($order->total_pengeluaran) && $order->total_pengeluaran > 0 ? number_format((int) $order->total_pengeluaran, 0, ',', '.') : '' }}"
                                        placeholder="0" inputmode="numeric"
                                        oninput="
                                            let raw = this.value.replace(/[^0-9]/g, '').slice(0, 6);
                                            document.getElementById('counter_total_pengeluaran').textContent = raw.length + '/6';
                                            document.getElementById('hidden_total_pengeluaran').value = raw;
                                            this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                        "
                                        class="w-full bg-surface border border-white/10 text-white text-sm text-right rounded-xl pl-10 pr-12 py-3 focus:border-primary placeholder:text-white/20 transition-colors">

                                    <span id="counter_total_pengeluaran"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">
                                        {{ isset($order->total_pengeluaran) && $order->total_pengeluaran > 0 ? strlen((string) (int) $order->total_pengeluaran) : '0' }}/6
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block">Upload
                                    Struk / Bukti Pembelian *</label>
                                <label for="struk_upload"
                                    class="flex items-center gap-3 bg-surface border border-white/10 hover:border-primary/50 text-white/70 text-xs rounded-xl px-4 py-3 cursor-pointer transition-all">
                                    <span class="material-symbols-outlined text-[18px] text-primary">upload_file</span>
                                    <span id="struk_filename">Pilih file struk (JPG/PNG)</span>
                                </label>
                                <input type="file" id="struk_upload" name="struk"
                                    accept="image/png, image/jpeg, image/jpg" class="hidden"
                                    onchange="document.getElementById('struk_filename').innerText = this.files[0] ? this.files[0].name : 'Pilih file struk (JPG/PNG)'">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-white/5 bg-surface-container-lowest flex justify-end gap-4">
                    <button type="button" onclick="closeRestockModal()"
                        class="px-6 py-3 border border-white/10 rounded-xl text-xs font-bold uppercase tracking-widest text-white hover:bg-white/5 transition-colors">
                        Batal
                    </button>
                    <button type="submit" onclick="closeRestockModal()"
                        class="px-6 py-3 bg-primary text-black rounded-xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-transform flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">print</span> Cetak Kertas Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT STOK & HARGA --}}
    <div id="editStockModal"
        class="fixed inset-0 z-[110] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
        <div
            class="bg-surface-container-low border border-white/10 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-white/5 bg-surface-container-highest/30 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-headline font-black text-white uppercase tracking-widest">Update Stok & Harga
                    </h2>
                    <p class="text-[10px] text-white/40 mt-0.5 uppercase tracking-widest">Penyesuaian Manual</p>
                </div>
                <button onclick="closeEditModal()" class="text-white/50 hover:text-white"><span
                        class="material-symbols-outlined">close</span></button>
            </div>

            <form action="{{ route('ops.inventory.update-stock') }}" method="POST" id="form_edit_stock" novalidate
                class="p-6">
                @csrf
                <input type="hidden" name="item_id" id="edit_item_id">
                <input type="hidden" name="type" id="edit_item_type">

                <p id="edit_item_name" class="text-primary font-bold text-sm mb-5 text-center"></p>

                <div class="flex flex-col gap-2 mb-4">
                    <label class="text-[10px] font-bold text-white/50 uppercase tracking-widest">Jumlah Stok Baru</label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-grow">
                            <input type="text" name="new_stock" id="edit_item_stock" inputmode="numeric"
                                placeholder="0"
                                oninput="
                                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); 
                                    document.getElementById('counter_edit_stock').textContent = this.value.length + '/3';
                                "
                                class="w-full bg-surface border border-white/10 text-white text-xl font-black text-center rounded-xl py-4 pr-12 focus:border-primary focus:ring-1 focus:ring-primary">

                            <span id="counter_edit_stock"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">0/3</span>
                        </div>
                        <span id="edit_item_unit" class="text-sm font-bold text-white/30 w-12">Pcs</span>
                    </div>
                    <span id="error_edit_item_stock" class="hidden text-red-500 text-[10px] mt-1"></span>
                </div>

                <div id="harga_jual_section" class="flex flex-col gap-2 mb-6 hidden">
                    <label class="text-[10px] font-bold text-white/50 uppercase tracking-widest flex items-center gap-2">
                        <span class="material-symbols-outlined text-[14px] text-primary">sell</span>
                        Harga Jual per Pcs (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/30 text-sm font-bold">Rp</span>

                        <input type="hidden" name="harga_jual" id="hidden_edit_harga_jual">

                        <input type="text" id="edit_harga_jual" placeholder="0" inputmode="numeric"
                            oninput="
                                let raw = this.value.replace(/[^0-9]/g, '').slice(0, 7);
                                document.getElementById('counter_edit_harga_jual').textContent = raw.length + '/7';
                                document.getElementById('hidden_edit_harga_jual').value = raw;
                                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            "
                            class="w-full bg-surface border border-white/10 text-white text-xl font-black text-right rounded-xl py-4 pl-10 pr-12 focus:border-primary focus:ring-1 focus:ring-primary">

                        <span id="counter_edit_harga_jual"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">0/7</span>
                    </div>
                    <p class="text-[9px] text-white/30 mt-1">
                        Harga ini yang akan digunakan sebagai dasar kalkulasi order pelanggan.
                    </p>
                </div>

                <button type="button" id="btn_simpan_edit_stok" onclick="simpanEditStok()"
                    class="w-full bg-primary text-black py-4 rounded-xl font-headline font-black uppercase text-xs tracking-[0.2em] hover:scale-[1.02] transition-transform shadow-lg">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH BARANG FISIK (APPAREL) --}}
    <div id="addApparelModal"
        class="fixed inset-0 z-[120] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
        <div
            class="bg-surface-container-low border border-white/10 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div
                class="p-6 border-b border-white/5 bg-surface-container-highest/30 flex justify-between items-center flex-shrink-0">
                <h2 class="text-lg font-headline font-black text-white uppercase tracking-widest">Tambah Item Gudang (SKU)
                </h2>
                <button onclick="closeAddApparelModal()" class="text-white/50 hover:text-white"><span
                        class="material-symbols-outlined">close</span></button>
            </div>

            <form action="{{ route('ops.inventory.store-apparel') }}" method="POST" id="form_add_apparel" novalidate
                class="overflow-y-auto custom-scrollbar flex-grow p-6 flex flex-col gap-5">
                @csrf

                <div class="flex flex-col gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block">Pilih
                            Produk Induk *</label>
                        <select name="product_id" id="add_product_id"
                            class="w-full bg-surface border border-white/10 text-white text-sm rounded-xl px-4 py-3 focus:border-primary">
                            <option value="" data-kategori="" selected>-- Pilih Kaos/Topi/Bendera --</option>
                            @foreach ($masterProducts as $mp)
                                <option value="{{ $mp->id }}"
                                    data-kategori="{{ strtolower($mp->category->name . ' ' . $mp->name) }}">
                                    {{ $mp->category->name }} - {{ $mp->name }}
                                </option>
                            @endforeach
                        </select>
                        <span id="error_add_product_id" class="hidden text-red-500 text-[10px] mt-1 block"></span>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block">Spesifikasi
                            Varian *</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">

                            <div id="wrap_var_lengan" class="hidden">
                                <label class="text-[9px] text-white/30 uppercase tracking-widest mb-1 block">Jenis
                                    Lengan</label>
                                <select id="add_var_lengan"
                                    class="w-full bg-surface border border-white/10 text-white text-xs rounded-xl px-3 py-3 focus:border-primary">
                                    <option value="">-- Tidak Ada --</option>
                                    <option value="Lengan Pendek">Lengan Pendek</option>
                                    <option value="Lengan Panjang">Lengan Panjang</option>
                                </select>
                            </div>

                            <div id="wrap_var_warna" class="hidden">
                                <label class="text-[9px] text-white/30 uppercase tracking-widest mb-1 block">Warna
                                    *</label>
                                <input type="text" id="add_var_warna" placeholder="Cth: Hitam"
                                    class="w-full bg-surface border border-white/10 text-white text-xs rounded-xl px-3 py-3 focus:border-primary">
                            </div>

                            <div id="wrap_var_ukuran_baju" class="hidden">
                                <label class="text-[9px] text-white/30 uppercase tracking-widest mb-1 block">Ukuran
                                    *</label>
                                <select id="add_var_ukuran_baju"
                                    class="w-full bg-surface border border-white/10 text-white text-xs rounded-xl px-3 py-3 focus:border-primary">
                                    <option value="">-- Pilih --</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                    <option value="3XL">3XL</option>
                                </select>
                            </div>

                            <div id="wrap_var_keterangan" class="hidden">
                                <label id="label_var_keterangan"
                                    class="text-[9px] text-white/30 uppercase tracking-widest mb-1 block">Keterangan Ukuran
                                    *</label>
                                <input type="text" id="add_var_keterangan" placeholder="Cth: 120x80 cm"
                                    class="w-full bg-surface border border-white/10 text-white text-xs rounded-xl px-3 py-3 focus:border-primary">
                            </div>

                        </div>
                        <p class="text-[9px] text-white/30 mt-2">Preview nama varian: <span id="add_var_preview"
                                class="text-primary font-bold">-</span></p>
                        <input type="hidden" name="variant_name" id="add_variant_name_hidden">
                        <span id="error_add_variant_name" class="hidden text-red-500 text-[10px] mt-1 block"></span>
                    </div>

                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block">Kode
                                SKU *</label>
                            <input type="text" name="sku" id="add_sku" placeholder="KOS-HTM-L"
                                class="w-full bg-surface border border-white/10 text-white text-sm rounded-xl px-4 py-3 focus:border-primary uppercase">
                            <span id="error_add_sku" class="hidden text-red-500 text-[10px] mt-1 block"></span>
                        </div>
                        <div class="w-1/2">
                            <label class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block">Stok
                                Awal *</label>
                            <div class="relative">
                                <input type="text" name="stock" id="add_stock" value="0" inputmode="numeric"
                                    oninput="
                                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);
                                        document.getElementById('counter_add_stock').textContent = this.value.length + '/3';
                                    "
                                    class="w-full bg-surface border border-white/10 text-white text-sm rounded-xl px-4 py-3 pr-12 focus:border-primary">
                                <span id="counter_add_stock"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">1/3</span>
                            </div>
                            <span id="error_add_stock" class="hidden text-red-500 text-[10px] mt-1 block"></span>
                        </div>
                    </div>

                    <div>
                        <label
                            class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2 block flex items-center gap-2">
                            <span class="material-symbols-outlined text-[14px] text-primary">sell</span>
                            Harga Jual per Pcs (Rp)
                            <span class="text-white/20 normal-case font-normal">— opsional</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-white/30 text-sm font-bold">Rp</span>
                            <input type="hidden" name="harga_jual" id="hidden_add_harga_jual" value="0">
                            <input type="text" id="add_harga_jual" value="0" placeholder="0"
                                inputmode="numeric"
                                oninput="
                                    let raw = this.value.replace(/[^0-9]/g, '').slice(0, 7);
                                    document.getElementById('counter_add_harga_jual').textContent = raw.length + '/7';
                                    document.getElementById('hidden_add_harga_jual').value = raw;
                                    this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                "
                                class="w-full bg-surface border border-white/10 text-white text-sm text-right rounded-xl pl-10 pr-12 py-3 focus:border-primary">
                            <span id="counter_add_harga_jual"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">1/7</span>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-primary text-black py-4 rounded-xl font-headline font-black uppercase text-xs tracking-[0.2em] hover:scale-[1.02] transition-transform shadow-lg mt-2">
                    Simpan Barang ke Gudang
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS VARIAN --}}
    <div id="deleteStockModal"
        class="fixed inset-0 z-[130] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
        <div
            class="bg-surface-container-high border border-white/10 rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center">
            <div
                class="w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center mx-auto mb-4 border border-red-500/30">
                <span class="material-symbols-outlined text-3xl">delete_sweep</span>
            </div>
            <h3 class="text-white font-headline font-bold text-lg mb-2">Hapus Varian?</h3>
            <p class="text-sm text-on-surface-variant mb-2 leading-relaxed">
                Varian <strong id="delete_item_name" class="text-primary"></strong> akan dihapus permanen dari gudang.
            </p>
            <p id="delete_error_message" class="hidden text-red-400 text-xs mb-4 leading-relaxed"></p>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-3 rounded-xl border border-white/10 text-white font-bold text-xs uppercase tracking-widest hover:bg-white/5 transition-all">Batal</button>
                <form id="form_delete_apparel" action="" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-red-500 text-white font-bold text-xs uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg">Ya,
                        Hapus</button>
                </form>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
    <script>
        window.pendingBatches = @json($pendingBatches ?? []);
    </script>
    <script src="{{ asset('js/admin/inventory.js') }}"></script>
@endpush
