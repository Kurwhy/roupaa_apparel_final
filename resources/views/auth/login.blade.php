<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ROUPAA | Selamat Datang</title>

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

    <div class="absolute inset-0 z-0 opacity-20 mix-blend-luminosity industrial-grid"></div>

    <a href="{{ route('home') }}" class="fixed top-8 left-8 z-50 flex items-center gap-2 group animate-fade-in">
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

    <div class="absolute inset-0 z-0 opacity-10 transition-transform duration-[20s] hover:scale-105">
        <img class="w-full h-full object-cover" src="{{ asset('img/sablon.png') }}" alt="Background Sablon Industri" />
        <div class="absolute inset-0 bg-gradient-to-t from-background via-background/50 to-transparent"></div>
    </div>

    <main
        class="w-full max-w-md p-8 md:p-10 bg-surface/80 backdrop-blur-md border border-outline-variant/30 rounded-xl shadow-2xl relative z-10 animate-fade-in mx-4">

        <div class="flex justify-center mb-8 animate-slide-up opacity-0" style="animation-delay: 0.1s;">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Roupaa"
                class="h-20 w-auto object-contain drop-shadow-md animate-float"
                onerror="this.outerHTML='<div class=\'h-16 w-32 bg-surface-container-high rounded flex items-center justify-center border border-primary/50 shadow-lg\'><span class=\'text-primary font-bold text-xl tracking-widest\'>ROUPAA</span></div>'">
        </div>

        <header class="text-center space-y-2 mb-10 animate-slide-up opacity-0" style="animation-delay: 0.2s;">
            <h1 class="text-on-background font-headline text-3xl font-bold tracking-tight">Selamat Datang</h1>
            <p class="text-on-surface-variant font-body text-xs tracking-wider opacity-80">
                Silakan masuk untuk mengakses sistem ROUPAA Apparel.
            </p>
        </header>

        <div id="alert-error"
            class="hidden bg-red-900/50 border border-red-500/50 text-red-200 px-4 py-3 rounded mb-6 text-xs text-center animate-pulse">
        </div>

        <form id="loginForm" novalidate class="space-y-6">
            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.3s;">
                <label
                    class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2 transition-transform duration-300 group-focus-within:translate-x-1"
                    for="email">Alamat Email</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary transition-colors duration-300 pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <svg class="w-4 h-4 text-surface-variant group-focus-within:text-primary transition-colors duration-300 mr-3"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                        </path>
                    </svg>
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 pl-0 placeholder:text-surface-variant/50 transition-all"
                        id="email" name="email" placeholder="masukkan email" required type="email" />
                </div>
            </div>

            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.4s;">
                <label
                    class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2 transition-transform duration-300 group-focus-within:translate-x-1"
                    for="password">Kata Sandi</label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary transition-colors duration-300 pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <svg class="w-4 h-4 text-surface-variant group-focus-within:text-primary transition-colors duration-300 mr-3"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 pl-0 placeholder:text-surface-variant/50 transition-all"
                        id="password" name="password" placeholder="masukkan kata sandi" required type="password" />
                </div>
            </div>

            <div class="pt-2 animate-slide-up opacity-0" style="animation-delay: 0.6s;">
                <button id="btnLogin"
                    class="w-full bg-primary py-4 rounded-lg text-on-primary font-headline font-bold text-sm uppercase tracking-[0.2em] transition-all duration-300 active:scale-[0.98] hover:brightness-110 shadow-[0_0_15px_rgba(242,202,80,0.3)] hover:shadow-[0_0_25px_rgba(242,202,80,0.5)] flex items-center justify-center gap-3 relative overflow-hidden group"
                    type="submit">
                    <span class="relative z-10 flex items-center gap-2">
                        Masuk
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </span>
                </button>
                <div class="text-center mt-4">
                    <a href="/forgot-password" class="text-sm text-on-surface-variant hover:text-primary transition">
                        Lupa password?
                    </a>
                </div>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-outline-variant/30 text-center animate-slide-up opacity-0"
            style="animation-delay: 0.7s;">
            <p class="text-on-surface-variant font-label text-[10px] tracking-widest uppercase">
                Belum Punya Akun? <a
                    class="text-primary font-bold hover:underline underline-offset-4 transition-all ml-1"
                    href="{{ route('register') }}">Daftar</a>
            </p>
        </div>
    </main>

    <footer class="absolute bottom-4 text-center z-10 w-full animate-fade-in opacity-50">
        <p class="text-[9px] font-label uppercase tracking-[0.3em] text-on-surface-variant">
            © {{ date('Y') }} ROUPAA APPAREL. SISTEM INTEGRASI PRODUKSI.
        </p>
    </footer>

    @if (session('success'))
        <div id="toast-success"
            class="fixed top-10 left-1/2 transform -translate-x-1/2 z-[100] flex items-center w-[calc(100%-2rem)] max-w-md p-4 text-on-background bg-surface/95 backdrop-blur-sm border border-emerald-500/50 rounded-xl shadow-[0_0_30px_rgba(16,185,129,0.3)] animate-fade-in"
            role="alert">
            <div
                class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-emerald-500 bg-emerald-500/10 rounded-lg">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                </svg>
            </div>
            <div class="ms-3 text-sm font-body tracking-wide leading-relaxed">{{ session('success') }}</div>
            <button type="button" onclick="closeToast()"
                class="ms-auto -mx-1.5 -my-1.5 bg-transparent text-surface-variant hover:text-on-background rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8 transition-colors">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
    @endif

    <script src="{{ asset('js/auth.js') }}"></script>

</body>

</html>
