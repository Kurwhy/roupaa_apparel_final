<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ROUPAA | Reset Password</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
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

    <div class="absolute inset-0 z-0 opacity-10">
        <img class="w-full h-full object-cover" src="{{ asset('img/sablon.png') }}" alt="Background" />
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
            <h1 class="text-on-background font-headline text-3xl font-bold tracking-tight">Buat Password Baru</h1>
            <p class="text-on-surface-variant font-body text-xs tracking-wider opacity-80">
                Untuk akun <span class="text-primary font-semibold">{{ $email }}</span>
            </p>
        </header>

        @if ($errors->any())
            <div
                class="mb-6 bg-red-900/40 border border-red-500/40 text-red-300 px-4 py-3 rounded-lg text-xs font-body tracking-wide">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/reset-password" novalidate class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.3s;">
                <label
                    class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2 transition-transform duration-300 group-focus-within:translate-x-1"
                    for="password">
                    Password Baru
                </label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary transition-colors duration-300 pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <svg class="w-4 h-4 text-surface-variant group-focus-within:text-primary transition-colors duration-300 mr-3"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 pl-0 placeholder:text-surface-variant/50 transition-all"
                        id="password" name="password" placeholder="minimal 8 karakter" required type="password"
                        minlength="8" />
                </div>
            </div>

            <div class="group animate-slide-up opacity-0" style="animation-delay: 0.4s;">
                <label
                    class="block font-label text-[10px] uppercase tracking-[0.2em] text-primary mb-2 transition-transform duration-300 group-focus-within:translate-x-1"
                    for="password_confirmation">
                    Konfirmasi Password
                </label>
                <div
                    class="relative border-b border-outline-variant/40 group-focus-within:border-primary transition-colors duration-300 pb-1 flex items-center bg-surface-container-low/50 px-3 pt-2 rounded-t-md">
                    <svg class="w-4 h-4 text-surface-variant group-focus-within:text-primary transition-colors duration-300 mr-3"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input
                        class="block w-full bg-transparent border-none focus:ring-0 text-on-background font-body py-2 pl-0 placeholder:text-surface-variant/50 transition-all"
                        id="password_confirmation" name="password_confirmation" placeholder="masukkan ulang password"
                        required type="password" />
                </div>
            </div>

            <div class="pt-2 animate-slide-up opacity-0" style="animation-delay: 0.5s;">
                <button type="submit"
                    class="w-full bg-primary py-4 rounded-lg text-on-primary font-headline font-bold text-sm uppercase tracking-[0.2em] transition-all duration-300 active:scale-[0.98] hover:brightness-110 shadow-[0_0_15px_rgba(242,202,80,0.3)] hover:shadow-[0_0_25px_rgba(242,202,80,0.5)] flex items-center justify-center gap-3 group">
                    <span class="relative z-10 flex items-center gap-2">
                        Simpan Password Baru
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </main>

    <footer class="absolute bottom-4 text-center z-10 w-full animate-fade-in opacity-50">
        <p class="text-[9px] font-label uppercase tracking-[0.3em] text-on-surface-variant">
            © {{ date('Y') }} ROUPAA APPAREL. SISTEM INTEGRASI PRODUKSI.
        </p>
    </footer>

</body>

</html>
