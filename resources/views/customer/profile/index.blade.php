<!DOCTYPE html>
<html class="dark scroll-smooth" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>Profil Saya | ROUPAA</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="{{ asset('js/tailwind-config/customer.js') }}"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <script>
        window.PROFILE_CONFIG = {
            apiDataUrl: "{{ route('profile.api.data') }}",
            updateUrl: "{{ route('profile.update') }}",
            updatePasswordUrl: "{{ route('profile.password.update') }}",
            csrfToken: "{{ csrf_token() }}",
            token: sessionStorage.getItem('access_token') || '',
        };
    </script>

    <style>
        .pw-input,
        .modal-input {
            width: 100%;
            background-color: #171717 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 12px;
            padding: 12px 16px;
            color: #f3f4f6 !important;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .pw-input:focus,
        .modal-input:focus {
            background-color: #262626 !important;
            /* Sedikit lebih terang saat diklik/fokus */
            border-color: rgba(242, 202, 80, 0.8) !important;
            box-shadow: 0 0 0 2px rgba(242, 202, 80, 0.2) !important;
        }

        .pw-input::placeholder,
        .modal-input::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .field-label-sm {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(242, 202, 80, 0.9);
            margin-bottom: 8px;
        }

        .field-err-sm {
            font-size: 11px;
            color: #ef4444;
            margin-top: 6px;
            display: none;
            font-weight: 500;
        }

        .glass-chip {
            background: rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        .glass-chip:hover {
            background: rgba(0, 0, 0, 0.25);
            border-color: rgba(242, 202, 80, 0.4);
            transform: translateY(-2px);
        }

        textarea::-webkit-scrollbar {
            width: 8px;
        }

        textarea::-webkit-scrollbar-track {
            background: transparent;
        }

        textarea::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
    </style>
</head>

<body class="font-body bg-background text-on-background min-h-screen flex flex-col relative overflow-x-hidden">

    <div class="fixed top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[140px] pointer-events-none z-0">
    </div>
    <div
        class="fixed bottom-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none z-0">
    </div>

    @include('components.navbar')

    <main class="flex-grow pt-28 pb-16 px-4 md:px-8 max-w-6xl mx-auto w-full relative z-10">

        <div
            class="fade-up d1 bg-surface-container-lowest border border-white/10 rounded-[2rem] overflow-hidden shadow-2xl mb-8 relative">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-primary/40 via-primary to-primary/40">
            </div>

            <div class="p-6 md:p-8 flex flex-col gap-6">

                <div class="flex flex-col lg:flex-row lg:items-center gap-8">

                    <div class="flex items-center gap-5 flex-shrink-0 lg:w-80">
                        <div class="relative flex-shrink-0 group cursor-default">
                            <div
                                class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/40 flex items-center justify-center shadow-[0_0_30px_rgba(242,202,80,0.1)] group-hover:shadow-[0_0_40px_rgba(242,202,80,0.2)] transition-all duration-500">
                                <span id="avatar-initials"
                                    class="font-headline font-black text-3xl text-primary skeleton w-10 h-10 block rounded-lg"></span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div id="display-nama"
                                class="font-headline font-black text-xl text-gray-100 uppercase tracking-tight leading-tight truncate mb-1">
                                <span class="skeleton w-32 h-6 block rounded"></span>
                            </div>
                            <div id="display-email" class="text-gray-400 text-sm truncate font-medium mb-1.5">
                                <span class="skeleton w-40 h-4 block rounded"></span>
                            </div>
                            <div id="display-member"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/5 border border-white/10 text-gray-400 text-[11px] font-semibold">
                                <span class="skeleton w-24 h-3 block rounded"></span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="hidden lg:block w-px h-24 bg-gradient-to-b from-transparent via-white/10 to-transparent flex-shrink-0">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1 min-w-0">
                        <div class="glass-chip rounded-2xl p-4 min-w-0">
                            <p
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px] text-primary/70">call</span> WhatsApp
                            </p>
                            <p id="display-wa" class="font-semibold text-gray-100 text-sm break-words">
                                <span class="skeleton w-24 h-4 block rounded"></span>
                            </p>
                        </div>
                        <div class="glass-chip rounded-2xl p-4 min-w-0">
                            <p
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px] text-primary/70">domain</span>
                                Organisasi
                            </p>
                            <p id="display-org" class="font-semibold text-gray-100 text-sm break-words">
                                <span class="skeleton w-20 h-4 block rounded"></span>
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row lg:flex-col items-center justify-center gap-5 flex-shrink-0 lg:w-32">
                        <div class="text-center w-full">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Kelengkapan</p>
                                <span id="progress-pct" class="font-headline font-black text-xs text-primary">0%</span>
                            </div>
                            <div
                                class="w-full h-2 bg-white/5 rounded-full overflow-hidden border border-white/5 shadow-inner">
                                <div id="progress-bar"
                                    class="h-full bg-gradient-to-r from-primary/80 to-primary rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(242,202,80,0.5)]"
                                    style="width:0%"></div>
                            </div>
                        </div>
                        <button onclick="openModal()"
                            class="w-full group flex items-center justify-center gap-2 bg-primary/10 hover:bg-primary text-primary hover:text-gray-900 border border-primary/30 hover:border-primary font-headline font-bold text-xs uppercase tracking-[0.1em] py-3.5 px-5 rounded-xl transition-all duration-300 hover:shadow-[0_0_20px_rgba(242,202,80,0.3)] hover:-translate-y-0.5 active:translate-y-0">
                            <span
                                class="material-symbols-outlined text-[18px] transition-transform group-hover:scale-110">edit_square</span>
                            <span>Edit Profil</span>
                        </button>
                    </div>

                </div>

                <div class="glass-chip rounded-2xl p-4 min-w-0">
                    <p
                        class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px] text-primary/70">location_on</span>
                        Alamat Pengiriman
                    </p>
                    <p id="display-alamat"
                        class="font-semibold text-gray-100 text-sm whitespace-normal leading-relaxed break-words">
                        <span class="skeleton w-full h-4 block rounded mb-1"></span>
                        <span class="skeleton w-2/3 h-4 block rounded"></span>
                    </p>
                </div>

            </div>
        </div>

        <div
            class="fade-up d2 bg-surface-container-lowest border border-white/10 rounded-[2rem] p-6 md:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-red-500/5 rounded-full blur-[50px] pointer-events-none">
            </div>

            <div class="flex items-center gap-4 mb-8">
                <div
                    class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-500/20 to-red-500/5 border border-red-500/30 flex items-center justify-center flex-shrink-0 shadow-[0_0_15px_rgba(239,68,68,0.1)]">
                    <span class="material-symbols-outlined text-red-400 text-[22px]"
                        style="font-variation-settings:'FILL' 1">shield_lock</span>
                </div>
                <div>
                    <h2 class="font-headline font-black text-lg text-gray-100 tracking-wide">Keamanan Akun</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Lindungi akun Anda dengan password kombinasi huruf besar dan
                        angka (Min. 8 Karakter).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="field-label-sm">Password Saat Ini</label>
                    <input id="pw-current" type="password" class="pw-input" placeholder="Masukkan password lama">
                    <p id="err-pw-current" class="field-err-sm"></p>
                </div>
                <div>
                    <label class="field-label-sm">Password Baru</label>
                    <input id="pw-new" type="password" class="pw-input" placeholder="Min. 8 karakter">
                    <p id="err-pw-new" class="field-err-sm"></p>
                </div>
                <div>
                    <label class="field-label-sm">Konfirmasi Password Baru</label>
                    <input id="pw-confirm" type="password" class="pw-input" placeholder="Ulangi password baru">
                    <p id="err-pw-confirm" class="field-err-sm"></p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-white/5">
                <button id="btn-save-pw"
                    class="flex items-center gap-2 bg-primary text-gray-900 py-3.5 px-8 rounded-xl font-headline font-black text-sm uppercase tracking-wider hover:brightness-110 hover:-translate-y-0.5 hover:shadow-[0_8px_20px_-6px_rgba(242,202,80,0.5)] active:translate-y-0 active:shadow-none transition-all duration-300">
                    <span class="material-symbols-outlined text-[18px]">key</span>
                    <span>Ubah Password</span>
                </button>
            </div>
        </div>

    </main>

    <div id="edit-modal"
        class="modal-hidden fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none"
        style="background:rgba(0,0,0,0.75); backdrop-filter:blur(8px);" onclick="handleModalBackdropClick(event)">

        <div id="modal-box"
            class="w-full max-w-2xl rounded-[2rem] shadow-2xl overflow-hidden bg-surface-container-lowest border border-white/10 transform scale-95 transition-all duration-300">
            <div class="h-1.5 w-full bg-gradient-to-r from-primary/50 via-primary to-primary/50"></div>

            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center shadow-inner">
                            <span class="material-symbols-outlined text-primary text-[22px]">manage_accounts</span>
                        </div>
                        <div>
                            <h3
                                class="font-headline font-black text-xl uppercase tracking-widest text-gray-100 leading-tight">
                                Edit Profil</h3>
                            <p class="text-xs text-gray-400 font-medium mt-1">Perbarui informasi identitas dan kontak
                                Anda.</p>
                        </div>
                    </div>
                    <button onclick="closeModal()"
                        class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 hover:rotate-90 transition-all duration-300">
                        <span class="material-symbols-outlined text-[24px]">close</span>
                    </button>
                </div>

                <div id="modal-alert"
                    class="hidden mb-6 bg-red-500/10 border border-red-500/30 p-4 rounded-xl flex items-start gap-3 text-red-400 text-sm leading-relaxed shadow-inner">
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="field-label-sm">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="f-nama" placeholder="Sesuai kartu identitas"
                                class="modal-input">
                            <p id="err-f-nama" class="field-err-sm"></p>
                        </div>
                        <div>
                            <label class="field-label-sm">No. WhatsApp <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <input type="text" id="f-wa" placeholder="Contoh: 08123456789"
                                    class="modal-input" inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13); document.getElementById('counter-f-wa').textContent = this.value.length + '/13';">
                                <span id="counter-f-wa"
                                    class="absolute right-3 text-[10px] font-mono text-on-surface-variant/50 select-none pointer-events-none">0/13</span>
                            </div>
                            <p id="err-f-wa" class="field-err-sm"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="field-label-sm">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="f-email" placeholder="nama@email.com" class="modal-input">
                            <p class="text-[10px] text-gray-500 mt-2 font-medium">Pastikan email aktif untuk
                                notifikasi.</p>
                            <p id="err-f-email" class="field-err-sm"></p>
                        </div>
                        <div>
                            <label class="field-label-sm">Organisasi / Brand <span
                                    class="text-gray-500 lowercase tracking-normal font-medium">(Opsional)</span></label>
                            <input type="text" id="f-org" placeholder="Instansi tempat Anda bernaung"
                                class="modal-input">
                            <p id="err-f-org" class="field-err-sm"></p>
                        </div>
                    </div>

                    <div>
                        <label class="field-label-sm">Alamat Pengiriman <span class="text-red-500">*</span></label>
                        <textarea id="f-alamat" rows="3"
                            placeholder="Nama Jalan, Gedung, RT/RW, Kelurahan, Kecamatan, Kota/Kab, Provinsi, Kode Pos" class="modal-input"
                            style="resize:none"></textarea>
                        <p id="err-f-alamat" class="field-err-sm"></p>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row gap-4 mt-10">
                    <button onclick="closeModal()"
                        class="w-full sm:w-auto px-8 py-3.5 border border-white/10 hover:border-white/30 text-gray-300 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl text-sm font-bold uppercase tracking-wider transition-all duration-300">
                        Batal
                    </button>
                    <button id="modal-save-btn" onclick="saveModal()"
                        class="w-full sm:flex-1 bg-primary text-gray-900 py-3.5 px-6 rounded-xl font-headline font-black text-sm uppercase tracking-widest hover:brightness-110 hover:-translate-y-0.5 hover:shadow-[0_8px_20px_-6px_rgba(242,202,80,0.5)] active:translate-y-0 active:shadow-none transition-all duration-300 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" id="modal-btn-icon">save</span>
                        <span id="modal-btn-label">Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/customer/profile.js') }}?v={{ filemtime(public_path('js/customer/profile.js')) }}"></script>

</body>

</html>
