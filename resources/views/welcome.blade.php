<!DOCTYPE html>
<html class="dark scroll-smooth overflow-x-hidden" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>ROUPAA | Vendor Sablon & Konveksi Jogja</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Space+Grotesk:wght@300;400;500;600;700;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="{{ asset('js/tailwind-config/landing.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body
    class="font-body selection:bg-primary selection:text-on-primary overflow-x-hidden bg-background text-on-background relative">

    <div
        class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-primary/5 blur-[150px] pointer-events-none z-0">
    </div>

    @include('components.navbar')

    <main class="relative z-10">

        {{-- HERO SECTION --}}
        <section
            class="relative min-h-screen pt-20 flex flex-col justify-center px-6 md:px-12 lg:px-24 overflow-hidden">
            <div class="absolute right-0 top-0 w-full md:w-3/5 h-full opacity-30 pointer-events-none z-0">
                <div class="w-full h-full bg-cover bg-center mix-blend-luminosity animate-[pulse_10s_ease-in-out_infinite]"
                    style="background-image: url('{{ asset('img/sablon.png') }}');"></div>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-background via-background/90 to-transparent md:hidden">
                </div>
                <div class="absolute inset-0 bg-gradient-to-l from-transparent to-background hidden md:block"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-background to-transparent"></div>
            </div>

            <div class="max-w-4xl relative z-10 reveal active">
                <div
                    class="inline-flex items-center gap-3 bg-surface-container-high/50 border border-primary/20 backdrop-blur-md px-5 py-2.5 rounded-full mb-6 shadow-lg">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span
                        class="font-headline text-primary tracking-[0.2em] uppercase text-[10px] md:text-xs font-bold">Premium
                        Custom Apparel Jogja</span>
                </div>

                <h1
                    class="font-headline text-3xl sm:text-5xl md:text-6xl lg:text-[5rem] font-black text-white leading-[1.05] tracking-tight mb-6 text-left drop-shadow-2xl">
                    WUJUDKAN DESAIN <br />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-yellow-200 to-primary animate-gradient-x">APPAREL
                        IMPIANMU</span>
                </h1>

                <p
                    class="font-body text-on-surface-variant max-w-2xl text-base md:text-lg leading-relaxed mb-10 opacity-90 border-l-4 border-primary/50 pl-5">
                    Mitra produksi terpercaya untuk seragam instansi, komunitas, hingga merchandise clothing brand. Kami
                    menjamin akurasi desain, kualitas sablon presisi, dan jahitan rapi untuk setiap helai pakaian Anda.
                </p>

                <div class="flex mt-2">
                    <a href="#" id="hero-btn"
                        class="relative inline-flex items-center justify-center gap-4 bg-primary text-on-primary px-10 py-5 md:px-12 md:py-6 rounded-full font-headline font-black uppercase tracking-[0.2em] text-sm md:text-base w-full sm:w-auto hover:scale-105 hover:brightness-110 transition-all duration-500 shadow-[0_0_30px_rgba(242,202,80,0.4)] hover:shadow-[0_0_50px_rgba(242,202,80,0.6)] group overflow-hidden">
                        <div
                            class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/40 to-transparent group-hover:animate-[shimmer_1.5s_infinite]">
                        </div>
                        <span class="relative z-10">Mulai Custom Project</span>
                        <span
                            class="material-symbols-outlined text-[24px] md:text-[28px] relative z-10 group-hover:translate-x-2 transition-transform duration-300">arrow_forward</span>
                    </a>
                </div>
            </div>

            <div
                class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-outline-variant/60 animate-bounce cursor-pointer z-20">
                <span class="font-label text-[9px] uppercase tracking-[0.3em] font-bold">Gulir ke Bawah</span>
                <span class="material-symbols-outlined text-lg">arrow_downward</span>
            </div>
        </section>

        {{-- TENTANG SECTION --}}
        <section id="tentang" class="py-32 px-6 md:px-12 lg:px-24 bg-surface-container-lowest relative z-10">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                <div class="lg:col-span-5 reveal">
                    <h2
                        class="font-headline text-3xl md:text-5xl font-black uppercase tracking-tighter mb-8 leading-tight text-white">
                        Partner Terbaik <br /> <span class="text-primary">Untuk Apparel Kamu</span>
                    </h2>
                    <div class="space-y-6">
                        <p class="font-body text-on-surface-variant leading-relaxed text-base md:text-lg">
                            ROUPAA Apparel hadir membuka ruang kolaborasi (<span class="text-primary font-medium">Open
                                Partner</span>) sebagai vendor sablon dan konveksi terpercaya di Jogja.
                        </p>
                        <p class="font-body text-on-surface-variant leading-relaxed text-base md:text-lg">
                            Mulai dari seragam kelas, baju angkatan (squad), pakaian panitia, hingga kebutuhan *clothing
                            line*. Kami mengelola siklus produksi Anda secara detail dan komunikatif.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-7 grid grid-cols-2 gap-4 md:gap-6 reveal delay-200 relative">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-primary/10 rounded-full blur-[80px] pointer-events-none">
                    </div>
                    <div
                        class="bg-surface-container-high/50 backdrop-blur-sm border border-white/5 p-6 md:p-10 rounded-3xl shadow-xl hover:-translate-y-2 transition-transform duration-500 text-center group">
                        <h3
                            class="font-headline text-3xl md:text-4xl lg:text-6xl font-black text-white group-hover:text-primary transition-colors mb-3">
                            1+</h3>
                        <p class="font-label text-xs uppercase tracking-widest text-primary/80 font-bold">Pesan Satuan
                            Bisa</p>
                    </div>
                    <div
                        class="bg-surface-container-high/50 backdrop-blur-sm border border-white/5 p-6 md:p-10 rounded-3xl shadow-xl hover:-translate-y-2 transition-transform duration-500 text-center translate-y-6 md:translate-y-10 group">
                        <h3
                            class="font-headline text-3xl md:text-4xl lg:text-6xl font-black text-white group-hover:text-primary transition-colors mb-3">
                            10K<span class="text-3xl">+</span></h3>
                        <p class="font-label text-xs uppercase tracking-widest text-primary/80 font-bold">Kapasitas
                            Lusinan</p>
                    </div>
                    <div
                        class="bg-surface-container-high/50 backdrop-blur-sm border border-white/5 p-6 md:p-10 rounded-3xl shadow-xl hover:-translate-y-2 transition-transform duration-500 text-center group">
                        <h3
                            class="font-headline text-3xl md:text-4xl lg:text-6xl font-black text-white group-hover:text-primary transition-colors mb-3">
                            100<span class="text-3xl">%</span></h3>
                        <p class="font-label text-xs uppercase tracking-widest text-primary/80 font-bold">Sablon
                            Berkualitas</p>
                    </div>
                    <div
                        class="bg-surface-container-high/50 backdrop-blur-sm border border-white/5 p-6 md:p-10 rounded-3xl shadow-xl hover:-translate-y-2 transition-transform duration-500 text-center translate-y-6 md:translate-y-10 group">
                        <h3
                            class="font-headline text-4xl md:text-6xl font-black text-white group-hover:text-primary transition-colors mb-3">
                            24/7</h3>
                        <p class="font-label text-xs uppercase tracking-widest text-primary/80 font-bold">Layanan
                            Responsif</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- LAYANAN SECTION --}}
        <section id="layanan" class="py-32 px-6 md:px-12 lg:px-24 bg-surface relative z-10 border-t border-white/5">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-16 md:mb-24 reveal">
                    <div class="md:col-span-8">
                        <h2
                            class="font-headline text-3xl md:text-5xl font-black uppercase tracking-tighter mb-6 text-white">
                            Layanan Produksi Kami</h2>
                        <p class="text-on-surface-variant max-w-xl text-lg border-l-2 border-primary/50 pl-4">
                            Didukung oleh keahlian tim dan standar mutu yang ketat untuk memastikan hasil cetak yang
                            memuaskan di setiap helai kain.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        class="bg-surface-container-low border border-white/5 p-6 md:p-10 lg:p-12 rounded-3xl flex flex-col h-full hover:-translate-y-3 transition-all duration-500 shadow-2xl hover:shadow-[0_20px_40px_rgba(0,0,0,0.5)] group relative overflow-hidden reveal delay-100">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-[50px] group-hover:bg-primary/20 transition-colors duration-500">
                        </div>
                        <div
                            class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 mb-8 group-hover:scale-110 transition-transform duration-500">
                            <span class="material-symbols-outlined text-primary text-3xl">format_paint</span>
                        </div>
                        <h3
                            class="font-headline text-2xl font-black uppercase mb-4 text-white group-hover:text-primary transition-colors">
                            Sablon Detail & Awet</h3>
                        <p class="font-body text-on-surface-variant leading-relaxed text-sm md:text-base">Berpengalaman
                            menangani berbagai jenis sablon yang memastikan warna keluar solid, tahan lama, dan tidak
                            mudah pecah walau dicuci berkali-kali.</p>
                    </div>

                    <div
                        class="bg-surface-container-low border border-white/5 p-6 md:p-10 lg:p-12 rounded-3xl flex flex-col h-full hover:-translate-y-3 transition-all duration-500 shadow-2xl hover:shadow-[0_20px_40px_rgba(0,0,0,0.5)] group relative overflow-hidden reveal delay-200">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-[50px] group-hover:bg-primary/20 transition-colors duration-500">
                        </div>
                        <div
                            class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 mb-8 group-hover:scale-110 transition-transform duration-500">
                            <span class="material-symbols-outlined text-primary text-3xl">layers</span>
                        </div>
                        <h3
                            class="font-headline text-2xl font-black uppercase mb-4 text-white group-hover:text-primary transition-colors">
                            Bahan Kaos Pilihan</h3>
                        <p class="font-body text-on-surface-variant leading-relaxed text-sm md:text-base">Kami
                            memilihkan blank apparel yang nyaman, menyerap keringat, dan sangat cocok dengan iklim
                            tropis serta gaya streetwear terkini.</p>
                    </div>

                    <div
                        class="bg-surface-container-low border border-white/5 p-6 md:p-10 lg:p-12 rounded-3xl flex flex-col h-full hover:-translate-y-3 transition-all duration-500 shadow-2xl hover:shadow-[0_20px_40px_rgba(0,0,0,0.5)] group relative overflow-hidden reveal delay-300">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-[50px] group-hover:bg-primary/20 transition-colors duration-500">
                        </div>
                        <div
                            class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 mb-8 group-hover:scale-110 transition-transform duration-500">
                            <span class="material-symbols-outlined text-primary text-3xl">groups</span>
                        </div>
                        <h3
                            class="font-headline text-2xl font-black uppercase mb-4 text-white group-hover:text-primary transition-colors">
                            Ramah Komunitas</h3>
                        <p class="font-body text-on-surface-variant leading-relaxed text-sm md:text-base">Sangat
                            memahami kebutuhan kaos angkatan, acara (event), atau seragam tim. Komunikasi santai, mudah,
                            dan selalu siap melayani revisi desain bersama Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- LOKASI SECTION --}}
        <section id="lokasi"
            class="py-32 px-6 md:px-12 lg:px-24 bg-surface-container-lowest border-t border-white/5 relative z-10">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal">
                    <h2
                        class="font-headline text-3xl md:text-5xl font-black uppercase tracking-tighter mb-8 leading-tight text-white">
                        Kunjungi <br /> <span class="text-primary">Workshop Kami</span>
                    </h2>
                    <p class="font-body text-on-surface-variant leading-relaxed mb-12 text-lg">Mari ngopi dan
                        berdiskusi! Silakan mampir ke workshop kami untuk melihat langsung sampel bahan, hasil sablon,
                        atau sekadar bertukar ide konsep seragam impian Anda.</p>

                    <div class="space-y-6">
                        <div
                            class="bg-surface-container-low border border-white/5 p-6 rounded-2xl flex items-start gap-5 shadow-lg">
                            <div
                                class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 border border-primary/30">
                                <span class="material-symbols-outlined text-primary">location_on</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white mb-2 tracking-wide uppercase text-sm font-headline">
                                    Alamat Workshop</h4>
                                <p class="text-on-surface-variant leading-relaxed text-sm">
                                    Jlegongan RT 04/ RW 12, Margodadi, Seyegan,<br />
                                    Kabupaten Sleman, Daerah Istimewa Yogyakarta 55561
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-surface-container-low border border-white/5 p-6 rounded-2xl flex items-start gap-5 shadow-lg">
                            <div
                                class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 border border-primary/30">
                                <span class="material-symbols-outlined text-primary">schedule</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white mb-2 tracking-wide uppercase text-sm font-headline">Jam
                                    Operasional</h4>
                                <p class="text-on-surface-variant leading-relaxed text-sm">
                                    Senin - Jumat: 09.00 - 17.00 WIB<br />
                                    Sabtu: 09.00 - 14.00 WIB
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative w-full rounded-3xl overflow-hidden border border-white/10 reveal delay-200 shadow-[0_0_40px_rgba(0,0,0,0.5)] group"
                    style="height: clamp(280px, 60vw, 500px)">
                    <iframe src="https://maps.google.com/maps?q=-7.7377923,110.2812609&z=17&output=embed"
                        width="100%" height="100%" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        style="border:0; filter: invert(90%) hue-rotate(180deg) grayscale(80%) contrast(1.2);"
                        class="pointer-events-none w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </iframe>
                    <div
                        class="absolute inset-0 z-10 bg-gradient-to-t from-background/80 via-background/20 to-transparent pointer-events-auto">
                    </div>
                    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 w-full px-8 z-20 flex justify-center">
                        <a href="https://www.google.com/maps/search/?api=1&query=Roupaa+Indonesia&query_place_id=ChIJyw0shH_3ei4RpI3VRlHilgA"
                            target="_blank" rel="noopener noreferrer"
                            class="bg-primary text-on-primary px-8 py-4 font-headline font-black uppercase tracking-widest text-sm rounded-full hover:scale-105 hover:brightness-110 transition-all duration-300 shadow-[0_0_20px_rgba(242,202,80,0.4)] flex items-center gap-3">
                            <span class="material-symbols-outlined text-xl">near_me</span>
                            Buka di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA SECTION --}}
        <section
            class="py-32 px-6 md:px-12 text-center bg-surface relative overflow-hidden border-t border-white/5 z-10">
            <div
                class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary/10 via-background to-background pointer-events-none">
            </div>

            <div class="max-w-5xl mx-auto relative z-10 reveal">

                <div class="mb-16">
                    <div
                        class="inline-flex items-center gap-2 bg-primary/10 border border-primary/20 px-4 py-1.5 rounded-full mb-4">
                        <span class="material-symbols-outlined text-primary text-[14px]">verified</span>
                        <span
                            class="font-headline text-primary tracking-[0.2em] uppercase text-[10px] font-bold">Portofolio
                            Kami</span>
                    </div>
                    <h2
                        class="font-headline text-4xl md:text-5xl font-black uppercase tracking-tighter leading-tight text-white drop-shadow-lg mb-12">
                        Karya Nyata <br /> <span class="text-primary">Dari Ide Pelanggan</span>
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="porto_grid">
                        <div class="porto-item aspect-square rounded-2xl overflow-hidden border border-white/10 group shadow-lg"
                            style="transition: opacity 0.5s ease;">
                            <img class="porto-img w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-80 group-hover:opacity-100"
                                src="" alt="">
                        </div>
                        <div class="porto-item aspect-square rounded-2xl overflow-hidden border border-white/10 group shadow-lg mt-0 md:mt-6"
                            style="transition: opacity 0.5s ease;">
                            <img class="porto-img w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-80 group-hover:opacity-100"
                                src="" alt="">
                        </div>
                        <div class="porto-item aspect-square rounded-2xl overflow-hidden border border-white/10 group shadow-lg"
                            style="transition: opacity 0.5s ease;">
                            <img class="porto-img w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-80 group-hover:opacity-100"
                                src="" alt="">
                        </div>
                        <div class="porto-item aspect-square rounded-2xl overflow-hidden border border-white/10 group shadow-lg mt-0 md:mt-6"
                            style="transition: opacity 0.5s ease;">
                            <img class="porto-img w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-80 group-hover:opacity-100"
                                src="" alt="">
                        </div>
                    </div>
                </div>

                {{-- GUEST BLOCK --}}
                <div id="guest-block" class="js-hidden">
                    <div
                        class="w-20 h-20 mx-auto bg-primary/10 rounded-full flex items-center justify-center border border-primary/30 mb-8 shadow-[0_0_20px_rgba(242,202,80,0.2)]">
                        <span class="material-symbols-outlined text-primary text-4xl">rocket_launch</span>
                    </div>
                    <h2
                        class="font-headline text-3xl md:text-4xl lg:text-6xl font-black uppercase tracking-tighter mb-8 leading-tight text-white drop-shadow-lg">
                        Wujudkan Desain <br /> <span class="text-primary">Seragam Kamu</span> Sekarang
                    </h2>
                    <p
                        class="font-body text-on-surface-variant text-base md:text-lg mb-12 max-w-2xl mx-auto leading-relaxed">
                        Daftarkan akun untuk mulai melakukan kustomisasi order. Unggah referensi Anda, pantau proses
                        *checking* desain, hingga status pengiriman pesanan dalam satu portal terpadu dan modern.
                    </p>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center gap-3 bg-primary text-on-primary px-10 py-5 font-headline font-black uppercase tracking-[0.2em] text-base rounded-full hover:scale-105 hover:brightness-110 transition-all duration-300 shadow-[0_0_30px_rgba(242,202,80,0.4)] group">
                        Buat Akun Portal
                        <span
                            class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>

                {{-- AUTH BLOCK --}}
                <div id="auth-block" class="js-hidden">
                    <a href="{{ route('customer.order.create') }}" id="auth-btn"
                        class="inline-flex items-center gap-3 bg-primary text-on-primary px-10 py-5 font-headline font-black uppercase tracking-[0.2em] text-base rounded-full hover:scale-105 hover:brightness-110 transition-all duration-300 shadow-[0_0_30px_rgba(242,202,80,0.4)] group">
                        Mulai Project Baru
                        <span
                            class="material-symbols-outlined group-hover:translate-x-1 transition-transform">add_circle</span>
                    </a>
                </div>

            </div>
        </section>

    </main>

    @include('components.footer')

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

    <script src="{{ asset('js/landing.js') }}"></script>
</body>

</html>
