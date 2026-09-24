<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>ROUPAA | Inisiasi Project</title>

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
    class="font-body selection:bg-primary-container selection:text-on-primary-container bg-background text-on-background">

    @include('components.navbar')

    <main class="pt-24 md:pt-32 pb-24 px-4 md:px-8 min-h-screen relative overflow-x-hidden">
        <div class="absolute inset-0 industrial-grid pointer-events-none opacity-50"></div>
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-primary/5 blur-[120px] pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 relative z-10">

            <div class="lg:col-span-8 space-y-10">

                <header class="text-left border-l-[3px] border-primary pl-6 mb-4">
                    <span
                        class="text-primary font-headline uppercase tracking-[0.2em] text-xs font-bold mb-2 block">Tahap
                        1: Inisiasi Order</span>
                    <h1
                        class="text-4xl md:text-5xl font-headline font-black uppercase tracking-tight text-white leading-tight">
                        Mulai Project Anda</h1>
                    <p class="text-on-surface-variant text-sm mt-2 max-w-lg">Lengkapi informasi dasar di bawah ini untuk
                        membuka ruang diskusi dengan tim desain kami.</p>
                </header>

                <div id="error-container"
                    class="hidden bg-red-900/20 border border-red-500/30 p-5 rounded-xl items-start gap-4 mb-4">
                    <span class="material-symbols-outlined text-red-400 mt-0.5">error</span>
                    <div>
                        <h3 class="text-red-400 font-bold uppercase tracking-widest mb-2 text-xs">Gagal Menginisiasi
                            Project</h3>
                        <ul id="error-list" class="text-red-200 text-xs list-disc list-inside space-y-1"></ul>
                    </div>
                </div>

                <form action="{{ route('customer.order.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8" id="order-form" novalidate>

                    {{-- SECTION 1: Kategori Produk --}}
                    <section
                        class="bg-surface-container-lowest border border-outline-variant/20 p-6 md:p-8 rounded-2xl shadow-lg">
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="w-12 h-12 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center flex-shrink-0 shadow-[0_0_15px_rgba(242,202,80,0.15)]">
                                <span class="text-primary font-headline text-xl font-bold">1</span>
                            </div>
                            <h2
                                class="font-headline text-xl md:text-2xl uppercase tracking-widest font-bold text-white">
                                Kategori Produk</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($categories as $index => $category)
                                <label class="group cursor-pointer">
                                    <input {{ $index == 0 ? 'checked' : '' }} class="hidden peer category-radio"
                                        name="category_id" type="radio" value="{{ $category->id }}" />
                                    <div
                                        class="p-6 bg-surface-container-low border border-outline-variant/20 rounded-xl transition-all duration-300
                                        group-hover:bg-surface-container-high group-hover:border-primary/50
                                        peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:shadow-[0_0_20px_rgba(242,202,80,0.15)]
                                        h-full flex flex-col items-center text-center gap-4">
                                        <span
                                            class="material-symbols-outlined text-4xl text-on-surface-variant group-hover:text-primary peer-checked:text-primary transition-colors">
                                            {{ $category->icon ?? 'category' }}
                                        </span>
                                        <h3
                                            class="font-headline font-bold uppercase tracking-widest text-xs text-on-surface-variant group-hover:text-white peer-checked:text-white">
                                            {{ $category->name }}
                                        </h3>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    {{-- SECTION 2: Jenis Spesifik --}}
                    <section
                        class="bg-surface-container-lowest border border-outline-variant/20 p-6 md:p-8 rounded-2xl shadow-lg">
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="w-12 h-12 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center flex-shrink-0 shadow-[0_0_15px_rgba(242,202,80,0.15)]">
                                <span class="text-primary font-headline text-xl font-bold">2</span>
                            </div>
                            <h2
                                class="font-headline text-xl md:text-2xl uppercase tracking-widest font-bold text-white">
                                Jenis Spesifik</h2>
                        </div>

                        <div class="relative w-full max-w-2xl" id="custom_dropdown_wrapper">
                            <input type="hidden" name="product_id" id="hidden_product_id" required>
                            <button type="button" id="dropdown_button"
                                class="w-full bg-surface-container-low border border-outline-variant/30 text-on-surface font-body py-4 px-5 transition-all rounded-xl flex justify-between items-center hover:border-primary focus:outline-none focus:border-primary shadow-inner">
                                <span id="dropdown_label" class="text-on-surface-variant opacity-70 text-sm">-- Pilih
                                    Kategori Terlebih Dahulu --</span>
                                <span class="material-symbols-outlined text-outline transition-transform duration-300"
                                    id="dropdown_arrow">expand_more</span>
                            </button>
                            <div id="dropdown_menu"
                                class="absolute z-50 w-full mt-2 bg-surface-container-high border border-outline-variant/30 rounded-xl shadow-2xl opacity-0 invisible translate-y-[-10px] transition-all duration-300 overflow-hidden max-h-60 overflow-y-auto">
                                <ul id="dropdown_list" class="py-2 text-sm text-on-surface font-body">
                                    <li class="px-5 py-4 text-on-surface-variant opacity-50 cursor-not-allowed">Pilih
                                        Kategori Terlebih Dahulu</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    {{-- SECTION 3: Detail Referensi --}}
                    <section
                        class="bg-surface-container-lowest border border-outline-variant/20 p-6 md:p-8 rounded-2xl shadow-lg">
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="w-12 h-12 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center flex-shrink-0 shadow-[0_0_15px_rgba(242,202,80,0.15)]">
                                <span class="text-primary font-headline text-xl font-bold">3</span>
                            </div>
                            <h2
                                class="font-headline text-xl md:text-2xl uppercase tracking-widest font-bold text-white">
                                Detail Referensi</h2>
                        </div>

                        <div class="space-y-8">
                            <div>
                                <label
                                    class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-3 pl-1">
                                    Nama Project / Artikel <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="project_name" value="{{ old('project_name') }}" required
                                    placeholder="Misal: Kaos Panitia 17-an"
                                    class="w-full bg-surface-container-low border border-outline-variant/30 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface font-body py-4 px-5 transition-all rounded-xl shadow-inner">
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="flex flex-col">
                                    <label
                                        class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-3 pl-1">Upload
                                        Desain (Opsional)</label>
                                    <div
                                        class="group relative flex flex-col items-center justify-center border-2 border-dashed border-outline-variant/30 bg-surface-container-low p-6 hover:border-primary hover:bg-primary/5 transition-all rounded-xl overflow-hidden h-56 shadow-inner">
                                        <input name="design_file" id="design_file_input"
                                            accept="image/png, image/jpeg, image/jpg, image/webp"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                                            type="file" />
                                        <div id="upload_placeholder"
                                            class="flex flex-col items-center pointer-events-none transition-opacity duration-300">
                                            <div
                                                class="w-12 h-12 bg-surface-container-high rounded-full flex items-center justify-center mb-4 group-hover:-translate-y-2 transition-transform duration-300 border border-outline-variant/20 shadow-md">
                                                <span
                                                    class="material-symbols-outlined text-2xl text-primary">cloud_upload</span>
                                            </div>
                                            <p
                                                class="font-headline uppercase text-xs tracking-widest mb-1 text-white font-bold">
                                                Upload Referensi</p>
                                            <p class="text-[10px] text-on-surface-variant mb-4 text-center px-4">Max
                                                10MB. (JPG, PNG, WEBP)</p>
                                            <button type="button"
                                                class="border border-primary-container px-6 py-2 font-headline text-primary uppercase text-[10px] tracking-widest transition-all rounded-lg hover:bg-primary hover:text-black">Pilih
                                                File</button>
                                        </div>
                                        <div id="preview_container"
                                            class="absolute inset-0 w-full h-full hidden bg-background flex items-center justify-center z-10 pointer-events-none">
                                            <img id="design_file_preview" src="" alt="Preview Desain"
                                                class="w-full h-full object-contain p-2" />
                                            <div
                                                class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                                <span
                                                    class="text-white text-xs font-bold uppercase tracking-widest flex items-center gap-2 bg-black/50 px-4 py-2 rounded-lg border border-white/20">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                                    Ganti Foto
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col">
                                    <label
                                        class="block text-[10px] font-bold uppercase tracking-widest text-primary mb-3 pl-1">Ide
                                        / Konsep Kasar (Opsional)</label>
                                    <textarea name="notes"
                                        placeholder="Misal: Tolong buatkan desain minimalis dengan warna dasar hitam, logo di dada kiri..."
                                        class="w-full flex-grow bg-surface-container-low border border-outline-variant/30 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface font-body p-5 transition-all rounded-xl resize-none shadow-inner"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>
                    <div class="lg:hidden">
                        <button type="submit"
                            class="w-full gold-gradient text-on-primary py-4 font-headline font-black uppercase text-sm tracking-[0.2em] rounded-xl hover:scale-[1.02] transition-transform shadow-[0_0_20px_rgba(242,202,80,0.2)] flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">rocket_launch</span> Mulai Order
                        </button>
                    </div>
                </form>
            </div>

            {{-- SIDEBAR: Ringkasan Tiket --}}
            <div class="lg:col-span-4 relative">
                <div class="sticky top-32 space-y-6 h-max">
                    <div
                        class="bg-surface-container-low rounded-2xl border border-outline-variant/20 shadow-2xl relative overflow-hidden flex flex-col">
                        <div class="h-1.5 w-full gold-gradient"></div>
                        <div class="p-6 md:p-8">
                            <h2
                                class="font-headline font-black text-xl uppercase tracking-widest mb-6 flex items-center gap-3 text-white border-b border-outline-variant/10 pb-4">
                                <span class="material-symbols-outlined text-primary">receipt_long</span>
                                Ringkasan Tiket
                            </h2>

                            <div class="space-y-4 mb-8 text-sm font-body">
                                <div
                                    class="flex justify-between items-center bg-surface-container-highest p-3 rounded-lg border border-outline-variant/10">
                                    <span
                                        class="text-on-surface-variant text-[10px] font-bold uppercase tracking-widest">Kategori</span>
                                    <span id="summary_kategori"
                                        class="font-bold text-white text-right text-xs">-</span>
                                </div>
                                <div
                                    class="flex justify-between items-center bg-surface-container-highest p-3 rounded-lg border border-outline-variant/10">
                                    <span
                                        class="text-on-surface-variant text-[10px] font-bold uppercase tracking-widest">Produk</span>
                                    <span id="summary_produk"
                                        class="font-bold text-primary text-right text-xs">-</span>
                                </div>
                                <div
                                    class="flex flex-col bg-surface-container-highest p-3 rounded-lg border border-outline-variant/10 gap-1.5">
                                    <span
                                        class="text-on-surface-variant text-[10px] font-bold uppercase tracking-widest">Nama
                                        Project</span>
                                    <span id="summary_project_name" class="font-bold text-white text-xs">-</span>
                                </div>
                                <div
                                    class="flex justify-between items-center bg-surface-container-highest p-3 rounded-lg border border-outline-variant/10">
                                    <span
                                        class="text-on-surface-variant text-[10px] font-bold uppercase tracking-widest">Lampiran</span>
                                    <span id="summary_referensi"
                                        class="font-bold text-white text-[10px] italic opacity-70">Belum ada
                                        file/catatan</span>
                                </div>
                            </div>

                            <div
                                class="mb-8 p-4 bg-primary/5 border border-primary/20 rounded-xl relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 opacity-10 pointer-events-none">
                                    <span class="material-symbols-outlined text-6xl">lock_clock</span>
                                </div>
                                <h3
                                    class="text-[10px] font-bold uppercase tracking-widest text-primary mb-2 flex items-center gap-2 relative z-10">
                                    <span class="material-symbols-outlined text-[14px]">info</span> Data Susulan
                                </h3>
                                <p class="text-[10px] text-on-surface-variant mb-3 leading-relaxed relative z-10">
                                    Akan diisi <strong class="text-white">di Ruang Project</strong> setelah desain
                                    disetujui:
                                </p>
                                <ul id="preview_attributes_list" class="space-y-2 relative z-10">
                                    <li class="text-[10px] text-on-surface-variant italic opacity-50">Silakan pilih
                                        produk terlebih dahulu...</li>
                                </ul>
                            </div>

                            <button type="submit" form="order-form"
                                class="w-full gold-gradient text-on-primary py-4 font-headline font-black uppercase text-sm tracking-[0.2em] rounded-xl hover:scale-[1.02] transition-transform shadow-[0_0_20px_rgba(242,202,80,0.2)] flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">rocket_launch</span> Mulai Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- Data Laravel untuk JS --}}
    <script>
        window.appCategoriesData = @json($categories);
    </script>
    <script src="{{ asset('js/customer/orders/custom-order.js') }}"></script>

</body>

</html>
