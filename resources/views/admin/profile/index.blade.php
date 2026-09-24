@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('header_title', 'Profil Saya')

@section('content')

    <style>
        .profile-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 10px 14px;
            color: #fff;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .profile-input:focus {
            border-color: rgba(242, 202, 80, 0.5);
        }

        .profile-input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .profile-input:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .field-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .field-row:last-child {
            border-bottom: none;
        }

        .field-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: rgba(242, 202, 80, 0.08);
            border: 1px solid rgba(242, 202, 80, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #f2ca50;
        }

        .save-btn {
            padding: 7px 16px;
            background: #f2ca50;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .save-btn:hover {
            opacity: 0.85;
        }

        .save-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .cancel-btn {
            padding: 7px 14px;
            background: transparent;
            color: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .cancel-btn:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .edit-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .edit-btn:hover {
            background: rgba(242, 202, 80, 0.1);
            border-color: rgba(242, 202, 80, 0.3);
            color: #f2ca50;
        }

        .pw-input-wrap {
            position: relative;
        }

        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .pw-toggle:hover {
            color: rgba(255, 255, 255, 0.7);
        }

        .skeleton {
            background: rgba(255, 255, 255, 0.06);
            border-radius: 6px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .badge-role {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .toast-profile {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header Card --}}
        <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-6 p-6 md:p-8 rounded-[20px] border border-primary/15"
            style="background:linear-gradient(135deg,rgba(242,202,80,0.08) 0%,rgba(255,255,255,0.02) 100%);">
            <div id="avatar_circle"
                style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,rgba(242,202,80,0.25),rgba(242,202,80,0.08));border:2px solid rgba(242,202,80,0.4);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:900;color:#f2ca50;flex-shrink:0;letter-spacing:-1px">
                ?
            </div>
            <div style="flex:1;min-width:0">
                <div class="flex items-center justify-center sm:justify-start gap-2.5 flex-wrap mb-1.5">
                    <h2 id="header_name" class="skeleton"
                        style="font-size:20px;font-weight:800;color:#fff;letter-spacing:.02em;border-radius:6px;min-width:120px;height:24px">
                    </h2>
                    <span id="header_role_badge" class="badge-role" style="display:none"></span>
                </div>
                <div class="flex items-center justify-center sm:justify-start gap-4 flex-wrap">
                    <span id="header_nip"
                        style="font-size:11px;color:rgba(255,255,255,0.35);font-family:monospace;letter-spacing:.08em"></span>
                    <span id="header_divisi" style="font-size:11px;color:rgba(255,255,255,0.4)"></span>
                    <span id="header_since" style="font-size:11px;color:rgba(255,255,255,0.3)"></span>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-5">

            {{-- Informasi Akun --}}
            <div class="flex-1"
                style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.07);border-radius:20px;padding:24px">
                <div style="margin-bottom:20px;display:flex;align-items:center;gap:10px">
                    <span class="material-symbols-outlined" style="font-size:18px;color:#f2ca50">manage_accounts</span>
                    <span
                        style="font-size:12px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.1em">Informasi
                        Akun</span>
                </div>

                {{-- Nama Lengkap --}}
                <div class="field-row">
                    <div class="field-icon">
                        <span class="material-symbols-outlined" style="font-size:16px">badge</span>
                    </div>
                    <div style="flex:1;min-width:0">
                        <p
                            style="font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">
                            Nama Lengkap</p>
                        <div id="display_nama" style="font-size:14px;color:#fff;font-weight:500" class="skeleton"
                            style="height:18px;width:140px"></div>
                        <input id="input_nama" type="text" class="profile-input" style="display:none;margin-top:6px"
                            placeholder="Nama lengkap">
                        <p id="err_nama" style="display:none;font-size:10px;color:#f87171;margin-top:4px"></p>
                    </div>
                    <button class="edit-btn" onclick="toggleEdit('nama')">
                        <span class="material-symbols-outlined" style="font-size:15px">edit</span>
                    </button>
                    <div id="actions_nama" style="display:none;gap:6px">
                        <button class="save-btn" onclick="saveField('nama')">Simpan</button>
                        <button class="cancel-btn" onclick="cancelEdit('nama')">Batal</button>
                    </div>
                </div>

                {{-- Email --}}
                <div class="field-row">
                    <div class="field-icon">
                        <span class="material-symbols-outlined" style="font-size:16px">mail</span>
                    </div>
                    <div style="flex:1;min-width:0">
                        <p
                            style="font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">
                            Email</p>
                        <div id="display_email" style="font-size:14px;color:#fff;font-weight:500;word-break:break-all;"
                            class="skeleton" style="height:18px;width:160px"></div>
                        <input id="input_email" type="email" class="profile-input" style="display:none;margin-top:6px"
                            placeholder="Alamat email aktif">
                        <div id="email_warning"
                            style="display:none;margin-top:6px;padding:8px 10px;background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.2);border-radius:8px;font-size:10px;color:#fbbf24;display:flex;align-items:center;gap:6px">
                            <span class="material-symbols-outlined" style="font-size:13px">warning</span>
                            Pastikan email yang dimasukkan aktif dan dapat menerima pesan.
                        </div>
                        <p id="err_email" style="display:none;font-size:10px;color:#f87171;margin-top:4px"></p>
                    </div>
                    <button class="edit-btn" onclick="toggleEdit('email')">
                        <span class="material-symbols-outlined" style="font-size:15px">edit</span>
                    </button>
                    <div id="actions_email" style="display:none;gap:6px">
                        <button class="save-btn" onclick="saveField('email')">Simpan</button>
                        <button class="cancel-btn" onclick="cancelEdit('email')">Batal</button>
                    </div>
                </div>

                {{-- Nomor Telepon --}}
                <div class="field-row">
                    <div class="field-icon">
                        <span class="material-symbols-outlined" style="font-size:16px">phone_iphone</span>
                    </div>
                    <div style="flex:1;min-width:0">
                        <p
                            style="font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">
                            Nomor Telepon</p>
                        <div id="display_telepon" style="font-size:14px;color:#fff;font-weight:500" class="skeleton"
                            style="height:18px;width:120px"></div>

                        <div id="wrap_input_telepon" class="relative" style="display:none; margin-top:6px;">
                            <input id="input_telepon" type="text" inputmode="numeric" maxlength="13"
                                pattern="[0-9]{10,13}"
                                oninput="
                                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
                                    document.getElementById('counter_telepon').textContent = this.value.length + '/13';
                                "
                                class="profile-input" style="padding-right: 48px; box-sizing: border-box;"
                                placeholder="10-13 digit angka">
                            <span id="counter_telepon"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-mono text-white/50 select-none pointer-events-none">0/13</span>
                        </div>
                        <p id="err_telepon" style="display:none;font-size:10px;color:#f87171;margin-top:4px"></p>
                    </div>
                    <button class="edit-btn" onclick="toggleEdit('telepon')">
                        <span class="material-symbols-outlined" style="font-size:15px">edit</span>
                    </button>
                    <div id="actions_telepon" style="display:none;gap:6px">
                        <button class="save-btn" onclick="saveField('telepon')">Simpan</button>
                        <button class="cancel-btn" onclick="cancelEdit('telepon')">Batal</button>
                    </div>
                </div>

                {{-- NIP (readonly) --}}
                <div class="field-row">
                    <div class="field-icon">
                        <span class="material-symbols-outlined" style="font-size:16px">tag</span>
                    </div>
                    <div style="flex:1;min-width:0">
                        <p
                            style="font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">
                            NIP Karyawan</p>
                        <div id="display_nip" style="font-size:14px;color:#fff;font-weight:500;font-family:monospace"
                            class="skeleton" style="height:18px;width:80px"></div>
                    </div>
                    <span
                        style="font-size:9px;color:rgba(255,255,255,0.2);text-transform:uppercase;letter-spacing:.1em">Auto</span>
                </div>

            </div>

            {{-- Keamanan --}}
            <div class="w-full lg:w-4/12 flex-shrink-0"
                style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.07);border-radius:20px;padding:24px">
                <div style="margin-bottom:20px;display:flex;align-items:center;gap:10px">
                    <span class="material-symbols-outlined" style="font-size:18px;color:#f2ca50">lock</span>
                    <span
                        style="font-size:12px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.1em">Keamanan</span>
                </div>

                <p style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:16px;line-height:1.6">
                    Password harus minimal 8 karakter dan mengandung huruf besar serta angka.
                </p>

                <div style="display:flex;flex-direction:column;gap:12px">
                    <div>
                        <label
                            style="display:block;font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Password
                            Saat Ini</label>
                        <div class="pw-input-wrap">
                            <input id="pw_current" type="password" class="profile-input"
                                placeholder="Masukkan password saat ini" style="padding-right:40px">
                            <button class="pw-toggle" onclick="togglePw('pw_current')">
                                <span class="material-symbols-outlined" style="font-size:16px">visibility</span>
                            </button>
                        </div>
                        <p id="err_pw_current" style="display:none;font-size:10px;color:#f87171;margin-top:4px"></p>
                    </div>

                    <div>
                        <label
                            style="display:block;font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Password
                            Baru</label>
                        <div class="pw-input-wrap">
                            <input id="pw_new" type="password" class="profile-input"
                                placeholder="Min. 8 karakter, huruf besar & angka" style="padding-right:40px">
                            <button class="pw-toggle" onclick="togglePw('pw_new')">
                                <span class="material-symbols-outlined" style="font-size:16px">visibility</span>
                            </button>
                        </div>
                        <p id="err_pw_new" style="display:none;font-size:10px;color:#f87171;margin-top:4px"></p>
                    </div>

                    <div>
                        <label
                            style="display:block;font-size:9px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Konfirmasi
                            Password Baru</label>
                        <div class="pw-input-wrap">
                            <input id="pw_confirm" type="password" class="profile-input"
                                placeholder="Ulangi password baru" style="padding-right:40px">
                            <button class="pw-toggle" onclick="togglePw('pw_confirm')">
                                <span class="material-symbols-outlined" style="font-size:16px">visibility</span>
                            </button>
                        </div>
                        <p id="err_pw_confirm" style="display:none;font-size:10px;color:#f87171;margin-top:4px"></p>
                    </div>

                    <div id="pw_strength" style="display:none;margin-top:4px">
                        <div style="height:3px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden">
                            <div id="pw_strength_bar"
                                style="height:100%;width:0;border-radius:4px;transition:width 0.3s,background 0.3s"></div>
                        </div>
                        <p id="pw_strength_label" style="font-size:9px;color:rgba(255,255,255,0.3);margin-top:4px"></p>
                    </div>

                    <button id="btn_save_pw" class="save-btn" onclick="savePassword()"
                        style="width:100%;padding:11px;margin-top:4px;font-size:12px">
                        Ubah Password
                    </button>
                </div>
            </div>

        </div>

    </div>

    <script>
        window.ADMIN_PROFILE_CONFIG = {
            token: sessionStorage.getItem('access_token'),
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
            apiDataUrl: '{{ route('ops.profile.admin.data') }}',
            updateUrl: '{{ route('ops.profile.admin.update') }}',
            updatePasswordUrl: '{{ route('ops.profile.admin.password') }}',
        };
    </script>

@endsection

@push('scripts')
    <script src="{{ asset('js/admin/profile.js') }}"></script>
@endpush
