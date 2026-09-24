<nav
    class="fixed top-0 left-0 w-full z-50 glass-overlay border-b border-outline-variant/20 px-6 py-5 md:px-8 md:py-6 flex justify-between items-center transition-all duration-300">

    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
        <img src="{{ asset('img/logo.png') }}" alt="Logo ROUPAA"
            class="h-8 md:h-9 w-auto object-contain transition-transform group-hover:scale-105">
        <span class="font-headline font-bold text-primary text-xl md:text-2xl tracking-widest uppercase">ROUPAA</span>
    </a>

    <div id="desktop-nav" style="gap:32px;align-items:center">
        <a href="{{ route('home') }}"
            class="text-xs font-bold uppercase tracking-widest transition-colors {{ request()->routeIs('home') ? 'text-primary' : 'text-on-background hover:text-primary' }}">
            Beranda
        </a>
        <a href="{{ route('customer.ai.index') }}"
            class="text-xs font-bold uppercase tracking-widest transition-colors flex items-center gap-2 {{ request()->routeIs('customer.ai.*') ? 'text-primary' : 'text-on-background hover:text-primary' }}">
            AI Design
        </a>
        <a href="{{ route('customer.order.create') }}"
            class="text-xs font-bold uppercase tracking-widest transition-colors {{ request()->routeIs('customer.order.create') ? 'text-primary' : 'text-on-background hover:text-primary' }}">
            Custom Order
        </a>
        <a href="{{ route('customer.order.history') }}"
            class="text-xs font-bold uppercase tracking-widest transition-colors {{ request()->routeIs('customer.order.history', 'customer.order.show') ? 'text-primary' : 'text-on-background hover:text-primary' }}">
            Progress Pesanan
        </a>
    </div>

    <div class="flex items-center gap-3">

        <div id="customer-notif-wrapper" style="display:none;position:relative">
            <button id="customer-notif-btn" onclick="toggleCustomerNotif()"
                style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.1);background:transparent;cursor:pointer;position:relative">
                <span class="material-symbols-outlined"
                    style="font-size:18px;color:rgba(255,255,255,0.6)">notifications</span>
                <span id="customer-notif-badge"
                    style="display:none;position:absolute;top:-4px;right:-4px;min-width:16px;height:16px;
                       background:#ef4444;color:#fff;font-size:9px;font-weight:900;
                       border-radius:50%;align-items:center;justify-content:center;padding:0 3px">
                    <span id="customer-notif-count">0</span>
                </span>
            </button>
            <div id="customer-notif-dropdown"
                style="display:none;position:absolute;right:0;top:52px;
                   width:min(360px, calc(100vw - 24px));
                   background:#0f0f0f;border:1px solid rgba(255,255,255,0.08);
                   border-radius:16px;z-index:9999;box-shadow:0 25px 60px rgba(0,0,0,0.95);overflow:hidden">
                <div
                    style="padding:12px 20px;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:space-between">
                    <span
                        style="font-size:11px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.1em">Notifikasi</span>
                    <button onclick="customerMarkAllRead()"
                        style="font-size:9px;color:#f2ca50;text-transform:uppercase;letter-spacing:.1em;font-weight:700;background:none;border:none;cursor:pointer">
                        Tandai Dibaca
                    </button>
                </div>
                <div id="customer-notif-list" style="max-height:380px;overflow-y:auto">
                    <div style="padding:32px;text-align:center">
                        <span class="material-symbols-outlined" style="color:rgba(255,255,255,0.1)">sync</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- desktop auth — wrapper hidden di mobile --}}
        <div id="desktop-auth-wrapper" style="align-items:center;gap:16px">
            <a href="{{ route('login') }}" id="nav-guest-btn"
                class="hidden bg-primary text-on-primary px-6 py-2.5 rounded-full font-headline font-bold text-xs uppercase tracking-widest hover:brightness-110 shadow-[0_0_15px_rgba(242,202,80,0.3)] transition-all">
                Masuk
            </a>

            <div class="relative hidden" id="nav-auth-menu">
                <button onclick="toggleUserMenu()"
                    class="flex items-center gap-3 bg-surface-container-low border border-white/10 px-4 py-2 rounded-full hover:border-primary/50 transition-colors focus:outline-none shadow-md group">
                    <div
                        class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center border border-primary/30 text-primary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                    <div class="text-left">
                        <p id="nav-user-name" class="text-xs font-bold text-white leading-none truncate max-w-[120px]">
                            Pelanggan</p>
                    </div>
                    <span
                        class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">expand_more</span>
                </button>
                <div id="user-dropdown"
                    class="absolute right-0 mt-3 w-56 bg-surface-container-highest border border-white/10 rounded-2xl shadow-2xl opacity-0 invisible translate-y-[-10px] transition-all duration-300 z-50 overflow-hidden">
                    <div class="p-4 border-b border-white/5 bg-surface/50">
                        <p id="nav-dropdown-name" class="text-xs font-bold text-white">Pelanggan</p>
                        <p id="nav-user-email" class="text-[10px] text-on-surface-variant truncate mt-1">user@email.com
                        </p>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('profile.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-[18px]">manage_accounts</span> Pengaturan Profil
                        </a>
                        <a href="{{ route('customer.order.history') }}"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-[18px]">receipt_long</span> Riwayat Pesanan
                        </a>
                    </div>
                    <div class="p-2 border-t border-white/5">
                        <button onclick="handleLogout()" type="button"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- hamburger mobile --}}
        <button id="hamburger-btn" onclick="toggleMobileMenu()"
            style="width:36px;height:36px;align-items:center;justify-content:center;border-radius:10px;border:1px solid rgba(255,255,255,0.15);background:transparent;cursor:pointer">
            <span class="material-symbols-outlined" style="font-size:20px;color:rgba(255,255,255,0.7)">menu</span>
        </button>
    </div>
    <style>
        @media (min-width: 768px) {
            #hamburger-btn {
                display: none !important;
            }

            #desktop-nav {
                display: flex !important;
            }

            #desktop-auth-wrapper {
                display: flex !important;
            }
        }

        @media (max-width: 767px) {
            #hamburger-btn {
                display: flex !important;
            }

            #desktop-nav {
                display: none !important;
            }

            #desktop-auth-wrapper {
                display: none !important;
            }
        }
    </style>
</nav>

{{-- mobile overlay --}}
<div id="mobile-overlay" onclick="closeMobileMenu()"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:55;backdrop-filter:blur(2px)"></div>

{{-- mobile drawer --}}
<div id="mobile-drawer"
    style="position:fixed;top:0;right:0;width:280px;height:100vh;background:#0f0f0f;border-left:1px solid rgba(255,255,255,0.08);z-index:56;transform:translateX(100%);transition:transform 0.3s ease;display:flex;flex-direction:column">

    <div
        style="padding:20px;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:space-between">
        <span
            style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.12em">Menu</span>
        <button onclick="closeMobileMenu()"
            style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center">
            <span class="material-symbols-outlined" style="font-size:18px;color:rgba(255,255,255,0.6)">close</span>
        </button>
    </div>

    <div style="padding:12px;flex:1;overflow-y:auto">
        <a href="{{ route('home') }}" onclick="closeMobileMenu()"
            style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;margin-bottom:4px;text-decoration:none;{{ request()->routeIs('home') ? 'background:rgba(242,202,80,0.1);color:#f2ca50;' : 'color:rgba(255,255,255,0.65);' }}">
            <span class="material-symbols-outlined" style="font-size:20px">home</span>
            <span style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.07em">Beranda</span>
        </a>
        <a href="{{ route('customer.ai.index') }}" onclick="closeMobileMenu()"
            style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;margin-bottom:4px;text-decoration:none;{{ request()->routeIs('customer.ai.*') ? 'background:rgba(242,202,80,0.1);color:#f2ca50;' : 'color:rgba(255,255,255,0.65);' }}">
            <span class="material-symbols-outlined" style="font-size:20px">auto_awesome</span>
            <span style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.07em">AI Design</span>
            <span
                style="background:rgba(242,202,80,0.15);color:#f2ca50;font-size:8px;padding:2px 6px;border-radius:4px;border:1px solid rgba(242,202,80,0.3);font-weight:700">PRO</span>
        </a>
        <a href="{{ route('customer.order.create') }}" onclick="closeMobileMenu()"
            style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;margin-bottom:4px;text-decoration:none;{{ request()->routeIs('customer.order.create') ? 'background:rgba(242,202,80,0.1);color:#f2ca50;' : 'color:rgba(255,255,255,0.65);' }}">
            <span class="material-symbols-outlined" style="font-size:20px">add_circle</span>
            <span style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.07em">Custom
                Order</span>
        </a>
        <a href="{{ route('customer.order.history') }}" onclick="closeMobileMenu()"
            style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;margin-bottom:4px;text-decoration:none;{{ request()->routeIs('customer.order.history', 'customer.order.show') ? 'background:rgba(242,202,80,0.1);color:#f2ca50;' : 'color:rgba(255,255,255,0.65);' }}">
            <span class="material-symbols-outlined" style="font-size:20px">receipt_long</span>
            <span style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.07em">Progress
                Pesanan</span>
        </a>
    </div>

    {{-- auth section mobile --}}
    <div style="padding:12px;border-top:1px solid rgba(255,255,255,0.06)">
        <a href="{{ route('login') }}" id="mobile-guest-btn"
            style="display:none;align-items:center;justify-content:center;gap:8px;padding:14px;border-radius:12px;background:#f2ca50;color:#000;text-decoration:none;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.07em">
            <span class="material-symbols-outlined" style="font-size:18px">login</span>
            Masuk
        </a>
        <div id="mobile-auth-section" style="display:none">
            <div style="padding:12px 16px;border-radius:12px;background:rgba(255,255,255,0.04);margin-bottom:8px">
                <p id="mobile-user-name" style="font-size:13px;font-weight:700;color:#fff;margin:0 0 2px">Pelanggan
                </p>
                <p id="mobile-user-email" style="font-size:11px;color:rgba(255,255,255,0.4);margin:0">user@email.com
                </p>
            </div>
            <a href="{{ route('profile.index') }}" onclick="closeMobileMenu()"
                style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:10px;text-decoration:none;color:rgba(255,255,255,0.65);margin-bottom:4px">
                <span class="material-symbols-outlined" style="font-size:18px">manage_accounts</span>
                <span style="font-size:13px;font-weight:600">Pengaturan Profil</span>
            </a>
            <button onclick="handleLogout()"
                style="width:100%;display:flex;align-items:center;gap:12px;padding:10px 16px;border-radius:10px;background:transparent;border:none;cursor:pointer;color:#f87171">
                <span class="material-symbols-outlined" style="font-size:18px">logout</span>
                <span style="font-size:13px;font-weight:600">Keluar</span>
            </button>
        </div>
    </div>
</div>

<script>
    function toggleMobileMenu() {
        const drawer = document.getElementById('mobile-drawer');
        const overlay = document.getElementById('mobile-overlay');
        const isOpen = drawer.style.transform === 'translateX(0px)' || drawer.style.transform === 'translateX(0%)' ||
            drawer.style.transform === 'translateX(0)';
        if (isOpen) {
            closeMobileMenu();
        } else {
            drawer.style.transform = 'translateX(0)';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMobileMenu() {
        document.getElementById('mobile-drawer').style.transform = 'translateX(100%)';
        document.getElementById('mobile-overlay').style.display = 'none';
        document.body.style.overflow = '';
    }
</script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script src="{{ asset('js/components/navbar.js') }}"></script>
<script src="{{ asset('js/customer/notifications.js') }}" defer></script>
