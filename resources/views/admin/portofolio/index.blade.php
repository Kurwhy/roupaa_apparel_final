@extends('layouts.admin')

@section('title', 'Album Portofolio')
@section('header_title', 'Album Portofolio')

@section('content')
    <div class="p-4 md:p-10 space-y-6 md:space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="font-headline text-3xl font-black text-white uppercase tracking-tight">Album Portofolio</h1>
                <p class="text-on-surface-variant text-sm">Kelola foto hasil produksi ROUPAA Apparel.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                <div
                    class="bg-surface-container-low border border-white/5 p-4 rounded-2xl min-w-[140px] shadow-lg flex-1 sm:flex-none">
                    <p class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">Total Foto</p>
                    <p class="text-2xl font-black text-primary" id="stat_total">—</p>
                </div>
                <button id="btn_tambah" onclick="openAddModal()"
                    class="flex justify-center items-center gap-2 px-5 py-4 sm:py-3 bg-primary text-background rounded-2xl text-xs font-bold hover:bg-primary/90 transition-all shadow-lg flex-1 sm:flex-none">
                    <span class="material-symbols-outlined text-[18px]">add_photo_alternate</span>
                    Tambah Foto
                </button>
            </div>
        </div>

        {{-- TABS --}}
        <div class="flex items-center gap-2">
            <button id="tab_aktif" onclick="switchTab('aktif')"
                class="px-5 py-2.5 text-xs font-bold rounded-xl transition-colors bg-primary text-background">
                Aktif
            </button>
            <button id="tab_terhapus" onclick="switchTab('terhapus')"
                class="px-5 py-2.5 text-xs font-bold rounded-xl transition-colors text-on-surface-variant hover:text-white hover:bg-white/5">
                Terhapus
            </button>
        </div>

        {{-- GALLERY GRID --}}
        <div id="photo_grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
            <div class="col-span-full flex justify-center py-16">
                <span class="material-symbols-outlined text-3xl text-white/10 animate-spin">sync</span>
            </div>
        </div>

    </div>
@endsection

@push('modals')
    {{-- MODAL TAMBAH --}}
    <div id="modal_add"
        class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[100] p-4">
        <div class="bg-surface-container-lowest border border-white/10 rounded-3xl w-full max-w-md shadow-2xl">
            <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                <h3 class="font-headline text-lg font-black text-white uppercase tracking-tight">Tambah Foto</h3>
                <button onclick="closeModal('modal_add')"
                    class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-on-surface-variant hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            <form id="form_add" onsubmit="submitAdd(event)" class="p-6 space-y-4">
                <div>
                    <label
                        class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Foto</label>
                    <input id="add_image" type="file" accept="image/*" required
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary file:text-background cursor-pointer">
                    <p class="text-[10px] text-on-surface-variant mt-1.5">JPG, PNG, WEBP — Maks. 5MB</p>
                </div>
                <div>
                    <label
                        class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Keterangan
                        <span class="text-white/30 normal-case font-normal">(opsional)</span></label>
                    <textarea id="add_keterangan" rows="3"
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-primary/50 transition-colors resize-none"
                        placeholder="Deskripsi singkat foto..."></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modal_add')"
                        class="flex-1 py-3 rounded-2xl border border-white/10 text-on-surface-variant hover:text-white hover:bg-white/5 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button id="btn_add_submit" type="submit"
                        class="flex-1 py-3 rounded-2xl bg-primary text-background text-xs font-bold hover:bg-primary/90 transition-colors shadow-lg">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="modal_edit"
        class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[100] p-4">
        <div class="bg-surface-container-lowest border border-white/10 rounded-3xl w-full max-w-md shadow-2xl">
            <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                <h3 class="font-headline text-lg font-black text-white uppercase tracking-tight">Edit Foto</h3>
                <button onclick="closeModal('modal_edit')"
                    class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-on-surface-variant hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            <form id="form_edit" onsubmit="submitEdit(event)" class="p-6 space-y-4">
                <input type="hidden" id="edit_id">
                <div>
                    <label
                        class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Ganti
                        Foto <span class="text-white/30 normal-case font-normal">(opsional)</span></label>
                    <input id="edit_image" type="file" accept="image/*"
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary file:text-background cursor-pointer">
                </div>
                <div>
                    <label
                        class="block text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold mb-2">Keterangan</label>
                    <textarea id="edit_keterangan" rows="3"
                        class="w-full bg-surface-container-high border border-white/10 rounded-2xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-primary/50 transition-colors resize-none"
                        placeholder="Deskripsi singkat foto..."></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modal_edit')"
                        class="flex-1 py-3 rounded-2xl border border-white/10 text-on-surface-variant hover:text-white hover:bg-white/5 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button id="btn_edit_submit" type="submit"
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
            <div id="confirm_icon_wrap" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border">
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

    {{-- IMAGE VIEWER MODAL --}}
    <div id="image_modal"
        class="fixed inset-0 z-[120] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm cursor-zoom-out" onclick="closeImageModal()"></div>
        <div class="relative z-10 flex flex-col items-center max-w-[90vw] max-h-[90vh]">
            <div class="w-full flex justify-end mb-4">
                <button onclick="closeImageModal()"
                    class="w-10 h-10 bg-white/10 text-white rounded-full flex items-center justify-center hover:bg-red-500 transition-colors border border-white/20">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <img id="modal_image_content" src=""
                class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-2xl scale-95 transition-transform duration-300">
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        window.portofolioApiUrl = "{{ route('ops.api.portofolio.data') }}";
        window.portofolioStoreUrl = "{{ route('ops.api.portofolio.store') }}";
        window.portofolioUpdateUrl = "{{ route('ops.api.portofolio.update', ['id' => '__ID__']) }}";
        window.portofolioDestroyUrl = "{{ route('ops.api.portofolio.destroy', ['id' => '__ID__']) }}";
        window.portofolioRestoreUrl = "{{ route('ops.api.portofolio.restore', ['id' => '__ID__']) }}";
    </script>
    <script src="{{ asset('js/admin/portofolio.js') }}"></script>
@endpush
