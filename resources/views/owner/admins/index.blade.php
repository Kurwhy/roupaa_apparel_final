@extends('layouts.admin')

@section('title', 'Kelola Akun Admin')
@section('header_title', 'Kelola Akun Admin')

@section('content')
    <div class="p-6 md:p-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-black text-white uppercase tracking-tight">Kelola Akun Admin</h1>
                <p class="text-on-surface-variant text-sm">Tambah, ubah, dan kelola akun staf admin ROUPAA Apparel.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-surface-container-low border border-white/5 p-4 rounded-2xl min-w-[140px] shadow-lg">
                    <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">Total Admin</p>
                    <p class="text-2xl font-black text-primary" id="stat_total_admin">—</p>
                </div>
                <button id="btn_tambah" onclick="openAddModal()"
                    class="flex items-center gap-2 px-5 py-3 bg-primary text-background rounded-2xl text-xs font-bold hover:bg-primary/90 transition-all shadow-lg">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Admin
                </button>
            </div>
        </div>

        {{-- TABS --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button id="tab_aktif" onclick="switchTab('aktif')"
                    class="px-5 py-2.5 text-xs font-bold rounded-xl transition-colors bg-primary text-background">
                    Admin Aktif
                </button>
                <button id="tab_terhapus" onclick="switchTab('terhapus')"
                    class="px-5 py-2.5 text-xs font-bold rounded-xl transition-colors text-on-surface-variant hover:text-white hover:bg-white/5">
                    Terhapus
                </button>
            </div>

            <div class="relative w-full sm:w-72">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-white/30 text-[18px]">search</span>
                <input type="text" id="search_input" placeholder="Cari nama, email, telepon, atau divisi..."
                    class="w-full bg-surface-container-low border border-white/10 text-white text-xs rounded-lg pl-9 pr-4 py-2.5 focus:border-primary placeholder:text-white/20 transition-all">
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-surface-container-lowest border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/10">
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Admin</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Kontak</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Divisi</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">NIP</th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Bergabung
                            </th>
                            <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-primary text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="admin_table_body" class="divide-y divide-white/5">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('modals')
    {{-- MODAL TAMBAH / EDIT --}}
    <div id="modal_overlay"
        class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[100] p-4">
        <div
            class="bg-surface-container-lowest border border-white/10 rounded-3xl w-full max-w-md shadow-2xl max-h-[90vh] overflow-y-auto">

            <div
                class="px-6 py-5 border-b border-white/5 flex items-center justify-between sticky top-0 bg-surface-container-lowest z-10">
                <h3 id="modal_title" class="font-headline text-lg font-black text-white uppercase tracking-tight">Tambah
                    Admin</h3>
                <button onclick="closeModal()"
                    class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-on-surface-variant hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <form id="admin_form" onsubmit="submitForm(event)" novalidate class="p-6 space-y-4">

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Nama
                        Lengkap</label>
                    <input id="field_nama" type="text" required
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-primary/50 transition-colors"
                        placeholder="Nama lengkap admin">
                </div>

                <div>
                    <label
                        class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Email</label>
                    <input id="field_email" type="email" required
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-primary/50 transition-colors"
                        placeholder="email@roupaa.com">
                </div>

                <div>
                    <label
                        class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Password</label>
                    <input id="field_password" type="password"
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-primary/50 transition-colors"
                        placeholder="Minimal 8 karakter">
                    <p id="password_hint" class="text-[10px] text-on-surface-variant mt-1.5"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">No.
                            Telepon</label>
                        <div class="relative flex items-center">
                            <input id="field_no_telepon" type="text" required inputmode="numeric" maxlength="13"
                                pattern="[0-9]{10,13}"
                                oninput="
                                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13); 
                                    document.getElementById('counter_no_telepon').textContent = this.value.length + '/13';
                                "
                                class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 pr-12 text-sm text-white placeholder-white/20 focus:outline-none focus:border-primary/50 transition-colors"
                                placeholder="Contoh: 08123456789">

                            <span id="counter_no_telepon"
                                class="absolute right-4 text-[10px] font-mono text-white/50 select-none pointer-events-none">0/13</span>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Divisi</label>
                        <select id="field_divisi" required
                            class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-primary/50 transition-colors">
                            <option value="Produksi">Produksi</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Desain">Desain</option>
                            <option value="Marketing">Marketing</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal()"
                        class="flex-1 py-3 rounded-2xl border border-white/10 text-on-surface-variant hover:text-white hover:bg-white/5 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button id="btn_submit" type="submit"
                        class="flex-1 py-3 rounded-2xl bg-primary text-background text-xs font-bold hover:bg-primary/90 transition-colors shadow-lg">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS / PULIHKAN --}}
    <div id="confirm_modal"
        class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[110] p-4">
        <div
            class="bg-surface-container-lowest border border-white/10 rounded-3xl w-full max-w-sm shadow-2xl p-7 text-center">
            <div id="confirm_icon_wrap"
                class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border">
                <span id="confirm_icon" class="material-symbols-outlined text-3xl"></span>
            </div>
            <h3 id="confirm_title" class="font-headline text-lg font-black text-white uppercase tracking-tight mb-2"></h3>
            <p id="confirm_message" class="text-sm text-on-surface-variant mb-6 leading-relaxed"></p>
            <div class="flex gap-3">
                <button type="button" onclick="closeConfirmModal()"
                    class="flex-1 py-3 rounded-2xl border border-white/10 text-on-surface-variant hover:text-white hover:bg-white/5 text-xs font-bold transition-colors">
                    Batal
                </button>
                <button id="confirm_action_btn" type="button" onclick="runConfirmedAction()"
                    class="flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg">
                </button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        window.adminsApiUrl = "{{ route('ops.owner.api.admins.data') }}";
        window.adminsStoreUrl = "{{ route('ops.owner.api.admins.store') }}";
        window.adminsUpdateUrl = "{{ route('ops.owner.api.admins.update', ['id' => '__ID__']) }}";
        window.adminsDestroyUrl = "{{ route('ops.owner.api.admins.destroy', ['id' => '__ID__']) }}";
        window.adminsRestoreUrl = "{{ route('ops.owner.api.admins.restore', ['id' => '__ID__']) }}";
    </script>
    <script src="{{ asset('js/owner/admins.js') }}"></script>
@endpush
