<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ROUPAA | Daftar Akun</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="{{ asset('js/tailwind-config/auth.js') }}"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body
    class="bg-background text-on-background min-h-screen flex flex-col items-center justify-center relative overflow-hidden selection:bg-primary selection:text-black">

    <div class="fixed inset-0 z-0 opacity-20 mix-blend-luminosity industrial-grid"></div>

    <a href="{{ route('home') }}"
        class="fixed top-4 left-4 md:top-8 md:left-8 z-50 flex items-center gap-2 group animate-fade-in">
        <div
            class="w-10 h-10 rounded-full border border-outline-variant/30 flex items-center justify-center bg-surface/50 backdrop-blur-sm group-hover:border-primary group-hover:bg-primary/10 transition-all duration-300">
            <svg class="w-5 h-5 text-on-surface-variant group-hover:text-primary transition-colors" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </div>
        <span
            class="text-[10px] font-headline font-bold uppercase tracking-[0.2em] text-on-surface-variant group-hover:text-primary transition-colors hidden md:block">
            Kembali ke Beranda
        </span>
    </a>

    <div class="fixed inset-0 z-0 opacity-10">
        <img class="w-full h-full object-cover" src="{{ asset('img/sablon.png') }}" alt="Background Sablon" />
        <div class="absolute inset-0 bg-gradient-to-t from-background via-background/80 to-transparent"></div>
    </div>

    <main
        class="w-full max-w-2xl p-8 bg-surface/80 backdrop-blur-md border border-outline-variant/30 rounded-xl shadow-2xl relative z-10 animate-fade-in mx-4 my-8">

        <div class="flex justify-center mb-6 animate-slide-up opacity-0" style="animation-delay: 0.1s;">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Roupaa"
                class="h-14 w-auto object-contain drop-shadow-md animate-float">
        </div>

        <header class="text-center space-y-1 mb-8 animate-slide-up opacity-0" style="animation-delay: 0.2s;">
            <h1 class="text-on-background font-headline text-3xl font-bold tracking-tight">Buat Akun Baru</h1>
            <p class="text-on-surface-variant font-body text-xs tracking-wider opacity-80">
                Daftarkan diri Anda untuk bergabung dengan sistem operasi ROUPAA.
            </p>
        </header>

        <form id="registerForm" novalidate class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.3s;">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2"
                    for="nama_lengkap">Nama Lengkap</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 text-sm"
                        id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap Anda" required type="text" />
                </div>
                <span id="error-nama_lengkap"
                    class="hidden text-red-500 text-[10px] uppercase mt-2 tracking-widest block text-left"></span>
            </div>

            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.4s;">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2"
                    for="no_whatsapp">No. WhatsApp</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 text-sm"
                        id="no_whatsapp" name="no_whatsapp" placeholder="Contoh: 08..." required type="text"
                        inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13); document.getElementById('wa_counter').textContent = this.value.length + '/13';" />
                    <span id="wa_counter"
                        class="flex-shrink-0 text-[10px] font-mono text-on-surface-variant/50 ml-2 select-none">0/13</span>
                </div>
                <span id="error-no_whatsapp"
                    class="hidden text-red-500 text-[10px] uppercase mt-2 tracking-widest block text-left"></span>
            </div>

            <div class="group md:col-span-2 animate-slide-up opacity-0" style="animation-delay: 0.5s;">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2"
                    for="email">Alamat Email</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 text-sm"
                        id="email" name="email" placeholder="masukkan email aktif" required type="email" />
                </div>
                <p
                    class="mt-2 text-[10px] text-on-surface-variant/60 font-label tracking-wider flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-primary/70 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
                    </svg>
                    Gunakan email aktif yang bisa kamu akses
                </p>
                <span id="error-email"
                    class="hidden text-red-500 text-[10px] uppercase mt-2 tracking-widest block text-left"></span>
            </div>

            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.6s;">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2"
                    for="password">Kata Sandi</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 text-sm"
                        id="password" name="password" placeholder="minimal 8 karakter" required type="password" />
                </div>
                <span id="error-password"
                    class="hidden text-red-500 text-[10px] uppercase mt-2 tracking-widest block text-left"></span>
            </div>

            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.7s;">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2"
                    for="password-confirm">Konfirmasi Sandi</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 text-sm"
                        id="password-confirm" name="password_confirmation" placeholder="masukkan ulang" required
                        type="password" />
                </div>
                <span id="error-password_confirmation"
                    class="hidden text-red-500 text-[10px] uppercase mt-2 tracking-widest block text-left"></span>
            </div>

            <div class="md:col-span-2 pt-2 animate-slide-up opacity-0" style="animation-delay: 0.8s;">
                <button id="btnRegister"
                    class="w-full bg-primary text-on-primary py-4 rounded-lg shadow-[0_0_15px_rgba(242,202,80,0.3)] font-headline font-bold text-sm uppercase tracking-[0.2em] transition-all duration-300 hover:brightness-110 flex items-center justify-center gap-3 group"
                    type="submit">
                    Daftar Akun
                </button>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-outline-variant/30 text-center animate-slide-up opacity-0"
            style="animation-delay: 0.9s;">
            <p class="text-on-surface-variant font-label text-[10px] tracking-widest uppercase">
                Sudah Punya Akun? <a class="text-primary font-bold hover:underline ml-1"
                    href="{{ route('login') }}">Masuk di sini</a>
            </p>
        </div>
    </main>

    <footer class="absolute bottom-4 text-center z-10 w-full animate-fade-in opacity-50">
        <p class="text-[9px] font-label uppercase tracking-[0.3em] text-on-surface-variant">
            © {{ date('Y') }} ROUPAA APPAREL. SISTEM INTEGRASI PRODUKSI.
        </p>
    </footer>

    <script src="{{ asset('js/auth.js') }}"></script>
</body>

</html>
