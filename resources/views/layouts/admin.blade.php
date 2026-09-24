<!DOCTYPE html>
<html class="dark scroll-smooth" lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | ROUPAA Management</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        (function checkTailwind() {
            if (typeof tailwind !== 'undefined') {
                var s = document.createElement('script');
                s.src = "{{ asset('js/tailwind-config/admin.js') }}";
                document.head.appendChild(s);
            } else {
                setTimeout(checkTailwind, 50);
            }
        })();
    </script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Space+Grotesk:wght@300;400;500;600;700;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    @vite(['resources/js/app.js'])

    <script>
        (function() {
            const token = sessionStorage.getItem('access_token');
            const role = sessionStorage.getItem('user_role');
            if (!token) {
                window.location.href = '/login';
            } else if (role !== 'admin' && role !== 'owner') {
                window.location.href = '/';
            }
        })();
    </script>

    <style>
        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateX(120px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-slide-in {
            animation: slide-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes notif-slide-in {
            from {
                opacity: 0;
                transform: translateX(120px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .notif-toast-in {
            animation: notif-slide-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        #sidebar {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
    </style>

    @stack('styles')
</head>

<body class="font-body bg-background text-on-background h-screen overflow-hidden flex">

    {{-- Overlay mobile --}}
    <div id="sidebar_overlay" onclick="closeSidebar()" class="hidden fixed inset-0 z-40 bg-black/70"
        style="backdrop-filter:blur(2px)"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
        class="fixed md:relative left-0 top-0 h-full w-64 bg-surface-container-lowest border-r border-white/5 flex flex-col flex-shrink-0 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 md:transition-none">

        {{-- Logo --}}
        <div class="h-16 md:h-20 flex items-center justify-between px-5 border-b border-white/5">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="Logo ROUPAA" class="h-7 md:h-8 w-auto">
                <span class="font-headline font-black text-white text-lg md:text-xl tracking-widest uppercase">
                    ROUPAA<span class="text-primary">.</span>
                </span>
            </a>
            <button onclick="closeSidebar()"
                class="md:hidden w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-white">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        {{-- Nav --}}
        <div class="flex-grow overflow-y-auto py-6 px-4 space-y-2">
            <p class="text-[10px] text-on-surface-variant/50 font-bold uppercase tracking-widest px-4 mb-2">Menu Utama
            </p>

            <a href="{{ route('ops.dashboard') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.dashboard') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined text-[20px]">dashboard</span> Dashboard
            </a>

            <a href="{{ route('ops.orders.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.orders.*') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span> Kelola Pesanan
            </a>

            <a href="{{ route('ops.inventory.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.inventory.*') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined text-[20px]">warehouse</span> Manajemen Inventori
            </a>

            <a href="{{ route('ops.customers.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.customers.*') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined text-[20px]">group</span> Data Pelanggan
            </a>

            <a href="{{ route('ops.portofolio.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.portofolio.*') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined text-[20px]">photo_library</span> Album Portofolio
            </a>

            {{-- Owner Area --}}
            <div id="owner_menu_area" class="hidden">
                <div class="pt-6 pb-2">
                    <p
                        class="text-[10px] text-primary/70 font-bold uppercase tracking-widest px-4 mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span> Owner Area
                    </p>
                </div>
                <a href="{{ route('ops.owner.laporan.index') }}" onclick="closeSidebar()"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.owner.laporan.*') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-primary hover:bg-primary/5' }}">
                    <span class="material-symbols-outlined text-[20px]">analytics</span> Laporan Keuangan
                </a>
                <a href="{{ route('ops.owner.admins.index') }}" onclick="closeSidebar()"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->routeIs('ops.owner.admins.*') ? 'bg-primary/10 text-primary font-medium' : 'text-on-surface-variant hover:text-primary hover:bg-primary/5' }}">
                    <span class="material-symbols-outlined text-[20px]">manage_accounts</span> Kelola Staff Admin
                </a>
            </div>
        </div>

        {{-- User Info --}}
        <div class="p-4 border-t border-white/5 bg-surface/30 relative">
            <div id="sidebar_user_dropdown"
                class="hidden absolute bottom-full left-4 right-4 mb-2 bg-surface-container-highest border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50">
                <a href="{{ route('ops.profile.admin.index') }}" onclick="closeSidebar()"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface-variant hover:text-primary hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">manage_accounts</span> Lihat Profil
                </a>
                <div class="border-t border-white/5"></div>
                <button onclick="handleAdminLogout()" type="button"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
                </button>
            </div>

            <button onclick="toggleSidebarUserMenu()" type="button"
                class="w-full flex items-center gap-3 px-2 py-1 rounded-xl hover:bg-white/5 transition-colors group">
                <div
                    class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center border border-primary/30 text-primary flex-shrink-0">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div class="overflow-hidden flex-1 text-left">
                    <p id="sidebar_user_name" class="text-xs font-bold text-white truncate">Loading...</p>
                    <p id="sidebar_user_role" class="text-[10px] text-primary uppercase tracking-widest">---</p>
                </div>
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">expand_less</span>
            </button>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-grow flex flex-col h-full overflow-hidden relative w-full">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[300px] bg-primary/5 blur-[120px] pointer-events-none z-0">
        </div>

        {{-- Top Header --}}
        <header
            class="h-16 md:h-20 border-b border-white/5 bg-surface/50 backdrop-blur-md flex items-center justify-between px-4 md:px-8 flex-shrink-0 relative z-40">
            <div class="flex items-center gap-3">
                {{-- Hamburger (mobile only) --}}
                <button onclick="openSidebar()"
                    class="md:hidden w-9 h-9 flex items-center justify-center rounded-xl bg-surface-container-high border border-white/10 text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">menu</span>
                </button>
                <h2 class="font-headline font-bold text-base md:text-xl text-white uppercase tracking-widest">
                    @yield('header_title', 'Dashboard')
                </h2>
            </div>

            <div class="flex items-center gap-3">
                {{-- Notifikasi --}}
                <div class="relative" style="z-index:999">
                    <button id="notif_bell_btn" onclick="toggleNotifDropdown()"
                        class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-surface-container-high border border-white/10 flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors relative">
                        <span class="material-symbols-outlined text-[18px] md:text-[20px]">notifications</span>
                        <span id="notif_badge"
                            class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center px-1">
                            <span id="notif_badge_count">0</span>
                        </span>
                    </button>

                    <div id="notif_dropdown"
                        class="hidden absolute right-0 top-12 md:top-14 bg-surface-container-lowest border border-white/10 rounded-2xl overflow-hidden"
                        style="z-index:9999;width:min(380px,90vw);box-shadow:0 25px 60px rgba(0,0,0,0.95)">
                        <div class="px-5 py-3 border-b border-white/5 flex items-center justify-between">
                            <span class="text-xs font-bold text-white uppercase tracking-widest">Notifikasi</span>
                            <button onclick="markAllNotifRead()"
                                class="text-[9px] text-primary uppercase tracking-widest font-bold hover:underline">
                                Tandai Dibaca
                            </button>
                        </div>
                        <div id="notif_list" class="max-h-[60vh] md:max-h-[420px] overflow-y-auto custom-scrollbar">
                            <div class="px-5 py-10 text-center">
                                <span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <div class="flex-grow overflow-y-auto p-4 md:p-8 relative z-10">
            @yield('content')
        </div>
    </main>

    <div id="toast_container" class="fixed top-20 right-4 md:top-24 md:right-6 flex flex-col gap-3"
        style="z-index:99999;pointer-events:auto"></div>

    <script>
        function openSidebar() {
            document.getElementById('sidebar').style.transform = 'translateX(0)';
            document.getElementById('sidebar_overlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            document.getElementById('sidebar').style.transform = '';
            document.getElementById('sidebar_overlay').classList.add('hidden');
            document.body.style.overflow = '';
            var dd = document.getElementById('sidebar_user_dropdown');
            if (dd) dd.classList.add('hidden');
        }

        function handleResize() {
            if (window.innerWidth >= 768) {
                document.getElementById('sidebar').style.transform = '';
                document.getElementById('sidebar_overlay').classList.add('hidden');
                document.body.style.overflow = '';
            } else {
                var overlay = document.getElementById('sidebar_overlay');
                if (overlay.classList.contains('hidden')) {
                    document.getElementById('sidebar').style.transform = '';
                }
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>

    <script src="{{ asset('js/admin/layout.js') }}"></script>
    @stack('scripts')
    @stack('modals')
</body>

</html>
