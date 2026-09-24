<!DOCTYPE html>
<html class="dark" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <title>ROUPAA | Ruang Project</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="{{ asset('js/tailwind-config/customer.js') }}"></script>
    {{-- Midtrans Snap JS (Sandbox) --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    {{-- Proteksi halaman --}}
    <script>
        (function() {
            const token = sessionStorage.getItem('access_token');
            const role = sessionStorage.getItem('user_role');
            if (!token) window.location.href = '/login';
            else if (role !== 'pelanggan') window.location.href = '/';
        })();
    </script>
</head>

<body
    class="font-body selection:bg-primary selection:text-black bg-background text-on-background min-h-screen md:h-screen overflow-x-hidden md:overflow-hidden flex flex-col relative">

    <div
        class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-[300px] bg-primary/5 blur-[120px] pointer-events-none z-0">
    </div>

    @include('components.navbar')

    <main
        class="flex-grow pt-24 px-4 md:px-8 pb-8 flex flex-col relative z-10 max-w-screen-2xl mx-auto w-full overflow-y-auto lg:overflow-hidden min-h-0 custom-scrollbar">

        <div id="page_loader"
            class="absolute inset-0 bg-background/80 backdrop-blur-sm z-50 flex flex-col items-center justify-center rounded-[2rem]">
            <span class="material-symbols-outlined text-primary text-5xl animate-spin mb-4">progress_activity</span>
            <p class="text-on-surface-variant font-headline tracking-widest uppercase animate-pulse">Menghubungkan ke
                Ruang Project...</p>
        </div>

        {{-- Header Project --}}
        <div
            class="flex-shrink-0 bg-surface-container-lowest border border-white/5 p-4 rounded-[2rem] shadow-lg relative overflow-hidden mb-4">
            <div class="absolute top-0 right-1/4 w-64 h-64 bg-primary/5 rounded-full blur-[60px] pointer-events-none">
            </div>
            <div class="flex flex-col md:flex-row md:items-center justify-between relative z-10 gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('customer.order.history') }}"
                        class="w-10 h-10 flex-shrink-0 rounded-full bg-surface border border-white/10 flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-black hover:border-primary transition-all shadow-sm group">
                        <span
                            class="material-symbols-outlined text-[20px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    </a>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3">
                            <h1 id="ui_project_name"
                                class="text-xl md:text-2xl font-headline font-black text-white uppercase tracking-tight leading-none">
                                Loading...</h1>
                            <span id="ui_order_number"
                                class="bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest">---</span>
                        </div>
                        <span id="ui_order_date"
                            class="text-[10px] text-on-surface-variant flex items-center gap-1.5 mt-1 font-medium tracking-wider">
                            <span class="material-symbols-outlined text-[13px]">calendar_today</span> ---
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div
                        class="inline-flex items-center gap-2.5 bg-surface/50 border border-primary/20 px-3.5 py-1.5 rounded-2xl shadow-[0_0_15px_rgba(242,202,80,0.05)]">
                        <div class="w-7 h-7 rounded-full bg-primary/20 text-primary flex items-center justify-center">
                            <span id="ui_status_icon" class="material-symbols-outlined text-[16px]">info</span>
                        </div>
                        <div class="flex flex-col pr-1">
                            <span
                                class="text-[8px] text-on-surface-variant uppercase tracking-widest font-bold leading-tight">Status</span>
                            <span id="ui_status_label"
                                class="text-[11px] font-black text-white uppercase tracking-wider leading-tight">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 flex-grow min-h-0 pb-10 lg:pb-0">

            {{-- KOTAK KIRI: CHAT --}}
            <div
                class="lg:col-span-7 flex flex-col bg-surface-container-lowest border border-white/20 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.6)] overflow-hidden relative h-[65vh] lg:h-full min-h-0">
                <div
                    class="p-5 border-b border-white/10 bg-surface/80 backdrop-blur-xl flex items-center justify-between relative z-20 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary border border-primary/20 relative shadow-[0_0_15px_rgba(242,202,80,0.1)]">
                            <span class="material-symbols-outlined text-[20px]">support_agent</span>
                        </div>
                        <div>
                            <h3 class="font-headline font-bold text-sm text-white uppercase tracking-widest">Tim Desain
                                ROUPAA</h3>
                            <p class="text-[10px] text-on-surface-variant mt-0.5">Siap membantu Anda</p>
                        </div>
                    </div>
                </div>

                <div id="chat_area"
                    class="flex-grow p-6 overflow-y-auto custom-scrollbar flex flex-col gap-6 z-10 bg-surface-container-lowest">
                    <div class="flex justify-center mb-4">
                        <span
                            class="bg-surface border border-white/10 text-on-surface-variant text-[10px] px-5 py-2 rounded-full uppercase tracking-widest text-center shadow-md">Ruang
                            Diskusi Pribadi</span>
                    </div>
                    <div id="chat_messages_container" class="flex flex-col gap-4"></div>
                </div>

                <div class="p-4 bg-surface/90 backdrop-blur-md border-t border-white/10 relative z-20 flex flex-col">
                    <div id="chat_file_preview_container" class="hidden mb-3 ml-14">
                        <div
                            class="relative inline-block w-16 h-16 rounded-xl overflow-hidden border border-white/20 group bg-surface-container-highest shadow-md transition-all duration-300 ease-out">
                            <img id="chat_file_preview_image" src="" class="w-full h-full object-cover hidden">
                            <div id="chat_file_preview_doc"
                                class="w-full h-full flex flex-col items-center justify-center text-primary bg-primary/10 hidden">
                                <span class="material-symbols-outlined text-[24px]">draft</span>
                            </div>
                            <button type="button" id="btn_remove_chat_file"
                                class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-red-500 hover:text-red-400 backdrop-blur-[2px]">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <form id="chat_form" action="/customer/progress-pesanan/{{ $id }}/chat"
                        class="flex items-end gap-3">
                        <label
                            class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-2xl bg-surface-container-highest border border-white/40 text-on-surface-variant hover:text-white hover:border-white cursor-pointer transition-all shadow-inner group/attach">
                            <span
                                class="material-symbols-outlined text-[22px] group-hover/attach:scale-110 transition-transform">attach_file</span>
                            <input type="file" name="attachment_file" id="chat_file_input" class="hidden"
                                accept="image/*">
                        </label>
                        <div class="flex-grow relative">
                            <textarea name="message" id="chat_message_input" rows="1" placeholder="Ketik pesan untuk Tim Desain..."
                                class="w-full bg-surface-container-lowest border border-white/40 hover:border-white/60 rounded-2xl pl-4 pr-12 py-3 text-sm leading-[22px] text-white focus:border-white focus:ring-1 focus:ring-white resize-none min-h-[48px] max-h-[120px] custom-scrollbar shadow-inner block transition-colors"></textarea>
                        </div>
                        <button type="submit" id="btn_send_chat"
                            class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-2xl bg-primary text-black hover:brightness-110 transition-all shadow-[0_0_20px_rgba(242,202,80,0.3)] hover:scale-105 active:scale-95">
                            <span class="material-symbols-outlined text-[20px] ml-1">send</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- KOTAK KANAN: KANVAS & SPESIFIKASI --}}
            <div class="lg:col-span-5 flex flex-col relative h-[70vh] lg:h-full min-h-0">

                {{-- PANEL 1: KANVAS DESAIN --}}
                <div id="panel_design_canvas"
                    class="absolute inset-0 bg-surface-container-lowest border border-white/20 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.6)] flex flex-col z-20 transition-all duration-700 ease-in-out overflow-hidden opacity-0 pointer-events-none scale-95">
                    <div
                        class="p-6 border-b border-white/10 bg-surface/40 backdrop-blur-md flex justify-between items-center">
                        <div>
                            <h3
                                class="font-headline font-black uppercase tracking-widest text-white text-base flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/30 text-primary">
                                    <span class="material-symbols-outlined text-[18px]">palette</span>
                                </div>
                                Kanvas Desain
                            </h3>
                            <p class="text-[10px] text-on-surface-variant uppercase mt-2 tracking-widest">Ruang Review
                                & Persetujuan</p>
                        </div>
                    </div>

                    <div class="flex-grow p-6 overflow-y-auto custom-scrollbar flex flex-col gap-8">
                        <div class="space-y-3">
                            <label
                                class="text-[11px] font-bold uppercase tracking-widest text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">visibility</span> Preview Desain
                                Final
                            </label>
                            <div id="ui_mockup_container" class="grid grid-cols-2 gap-3 w-full hidden"></div>
                            <div id="ui_no_mockup"
                                class="w-full h-56 bg-surface-container-high border-2 border-dashed border-white/20 rounded-2xl flex items-center justify-center relative overflow-hidden shadow-inner hidden">
                                <div class="text-center p-6 flex flex-col items-center">
                                    <div
                                        class="w-16 h-16 rounded-full bg-surface border border-white/10 flex items-center justify-center mb-4 opacity-50">
                                        <span
                                            class="material-symbols-outlined text-3xl text-on-surface-variant">hourglass_empty</span>
                                    </div>
                                    <p class="text-xs text-on-surface-variant/70 leading-relaxed max-w-[200px]">
                                        Menunggu tim desain mengunggah preview mockup produk Anda.</p>
                                </div>
                            </div>
                        </div>

                        <div id="ui_biaya_sablon_container" class="hidden">
                            <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 flex flex-col gap-2">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-widest text-primary flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">sell</span> Estimasi Biaya
                                    Sablon
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-on-surface-variant">Biaya sablon/pcs</span>
                                    <span id="ui_biaya_sablon_value"
                                        class="text-sm font-black text-primary font-mono">-</span>
                                </div>
                                <p class="text-[9px] text-white/30 leading-relaxed">Belum termasuk harga kain. Total
                                    akan dihitung otomatis saat Anda memilih varian di tahap spesifikasi.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label
                                class="text-[11px] font-bold uppercase tracking-widest text-on-surface-variant flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">history</span> Referensi Awal Anda
                            </label>
                            <div class="p-5 bg-surface-container-high border border-white/15 rounded-2xl shadow-inner">
                                <div id="ui_reference_image"
                                    class="hidden mb-4 rounded-xl overflow-hidden border border-white/20 cursor-pointer group relative shadow-md">
                                    <img src=""
                                        class="w-full h-auto max-h-48 object-contain bg-black/60 group-hover:opacity-50 transition-all duration-300 transform group-hover:scale-105">
                                </div>
                                <div id="ui_design_notes" class="hidden">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary mb-2">
                                        Catatan Ide / Konsep:</p>
                                    <div class="bg-surface p-4 rounded-xl border border-white/10">
                                        <p
                                            class="text-xs text-on-surface leading-relaxed font-medium whitespace-pre-wrap">
                                        </p>
                                    </div>
                                </div>
                                <p id="ui_no_reference"
                                    class="hidden text-xs text-on-surface-variant/50 leading-relaxed italic text-center py-6">
                                    Tidak ada referensi foto maupun catatan konsep yang dilampirkan.</p>
                            </div>
                        </div>
                    </div>

                    <div id="action_buttons_container"
                        class="p-6 border-t border-white/10 bg-surface-container-low flex-shrink-0 z-10"></div>
                </div>

                {{-- PANEL 2: SPESIFIKASI --}}
                <div id="panel_unlocked"
                    class="absolute inset-0 bg-surface border border-primary/40 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] flex flex-col z-10 transition-all duration-700 ease-in-out overflow-hidden opacity-0 pointer-events-none scale-95">
                    <div
                        class="p-6 border-b border-primary/20 bg-primary/5 flex justify-between items-center flex-shrink-0">
                        <div>
                            <h3
                                class="font-headline font-black uppercase tracking-widest text-primary text-base flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/40">
                                    <span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
                                </div>
                                Detail Spesifikasi
                            </h3>
                            <p
                                class="text-[10px] text-on-surface-variant uppercase mt-2 tracking-widest flex items-center gap-1">
                                Produk: <strong id="ui_spec_product_name" class="text-white">---</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Mockup collapsible --}}
                    <div id="ui_spec_mockup_container"
                        class="hidden border-b border-white/5 bg-surface-container-low flex-shrink-0">

                        {{-- Toggle button --}}
                        <button type="button" onclick="toggleMockupPreview()"
                            class="w-full px-6 py-3 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-primary hover:bg-primary/5 transition-colors">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[14px]">verified</span>
                                Desain Disetujui
                            </span>
                            <span id="mockup_toggle_icon"
                                class="material-symbols-outlined text-[16px] transition-transform">expand_more</span>
                        </button>

                        {{-- Foto mockup — default tersembunyi --}}
                        <div id="mockup_preview_photos" class="hidden px-6 pb-4">
                            <div id="ui_spec_mockup_list" class="flex gap-3 overflow-x-auto custom-scrollbar pb-2">
                            </div>
                        </div>
                    </div>

                    <div class="flex-grow p-5 overflow-y-auto custom-scrollbar bg-background/50">
                        <form id="spec_form" class="space-y-5">
                            <div id="spec_error_container"
                                class="hidden bg-red-900/20 border border-red-500/30 p-4 rounded-xl items-start gap-3">
                                <span class="material-symbols-outlined text-red-400 mt-0.5 text-[18px]">error</span>
                                <p id="spec_error_message" class="text-red-200 text-xs leading-relaxed"></p>
                            </div>
                            <div id="rows_container" class="space-y-5"></div>
                            <button type="button" onclick="addNewRow()"
                                class="w-full py-4 border-2 border-dashed border-primary/40 text-primary text-xs uppercase tracking-widest font-bold rounded-xl hover:bg-primary/10 hover:border-primary transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span> Tambah Kombinasi
                                Lain
                            </button>
                        </form>
                    </div>

                    <div
                        class="p-6 border-t border-primary/20 bg-surface-container-highest flex-shrink-0 relative overflow-hidden">
                        <div
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-1/2 bg-primary/10 blur-3xl pointer-events-none">
                        </div>
                        <div class="flex justify-between items-end mb-5 relative z-10">
                            <span class="text-xs uppercase tracking-widest text-on-surface-variant font-bold">Total
                                Pesanan</span>
                            <div class="text-right">
                                <span id="grand_total_qty"
                                    class="text-3xl md:text-4xl font-headline font-black text-primary leading-none">0</span>
                                <span
                                    class="text-sm font-bold text-primary/70 uppercase tracking-widest ml-1">Pcs</span>
                                <p id="grand_total_harga" class="text-xs text-primary/60 font-mono mt-1">-</p>
                            </div>
                        </div>
                        <button type="button" onclick="window.showPaymentSummary()"
                            class="w-full gold-gradient text-on-primary py-4 font-headline font-black uppercase text-sm tracking-[0.2em] rounded-xl hover:scale-[1.02] transition-transform shadow-[0_10px_25px_rgba(242,202,80,0.3)] relative z-10">
                            Lihat Rincian & Bayar
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    {{-- Modal Image --}}
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

    {{-- Modal Konfirmasi Approve Desain --}}
    <div id="modal_approve_design"
        class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
        <div
            class="bg-surface-container-high border border-white/10 rounded-3xl p-6 max-w-sm w-full shadow-2xl transform scale-100 transition-all text-center">
            <div
                class="w-16 h-16 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center mx-auto mb-4 border border-green-500/30">
                <span class="material-symbols-outlined text-3xl">verified</span>
            </div>
            <h3 class="text-white font-headline font-bold text-lg mb-2">Setujui Desain?</h3>
            <p class="text-sm text-on-surface-variant mb-6 leading-relaxed">Tindakan ini tidak dapat dibatalkan. Jika
                disetujui, pesanan Anda akan langsung masuk ke tahap <strong class="text-primary">Isi
                    Spesifikasi</strong>.</p>
            <div class="flex gap-3">
                <button type="button"
                    onclick="document.getElementById('modal_approve_design').classList.add('hidden')"
                    class="flex-1 py-3 rounded-xl border border-white/10 text-white font-bold text-xs uppercase tracking-widest hover:bg-white/5 transition-all">Batal</button>
                <button type="button" id="btn_confirm_approve" onclick="window.confirmApproveDesign()"
                    class="flex-1 py-3 rounded-xl bg-green-500 text-white font-bold text-xs uppercase tracking-widest hover:bg-green-600 transition-all shadow-lg">Ya,
                    Setujui</button>
            </div>
        </div>
    </div>

    {{-- Modal Sukses --}}
    <div id="modal_success_approve"
        class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
        <div
            class="bg-surface-container-high border border-white/10 rounded-3xl p-6 max-w-sm w-full shadow-2xl transform scale-100 transition-all text-center">
            <div
                class="w-16 h-16 rounded-full bg-primary/20 text-primary flex items-center justify-center mx-auto mb-4 border border-primary/30">
                <span class="material-symbols-outlined text-3xl">task_alt</span>
            </div>
            <h3 class="text-white font-headline font-bold text-lg mb-2">Desain Disetujui!</h3>
            <p class="text-sm text-on-surface-variant mb-6 leading-relaxed">Terima kasih! Silakan lanjutkan untuk
                mengisi detail spesifikasi produk Anda di panel sebelah kanan.</p>
            <button type="button" onclick="document.getElementById('modal_success_approve').classList.add('hidden')"
                class="w-full py-3 rounded-xl bg-primary text-black font-bold text-xs uppercase tracking-widest hover:brightness-110 transition-all shadow-lg">Selesai</button>
        </div>
    </div>

    {{-- Modal Rincian Harga & Pembayaran --}}
    <div id="payment_modal"
        class="fixed inset-0 z-[110] hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div
            class="bg-surface-container-low border border-white/10 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="p-6 border-b border-white/5 flex items-center justify-between">
                <h3
                    class="font-headline font-black text-lg text-white uppercase tracking-widest flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    Rincian Pesanan
                </h3>
                <button onclick="document.getElementById('payment_modal').classList.add('hidden')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-on-surface-variant hover:text-white hover:bg-white/10 transition-all">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Rincian item --}}
            <div class="p-6 max-h-64 overflow-y-auto custom-scrollbar">
                <div id="payment_summary_rows"></div>
            </div>

            {{-- Total --}}
            <div class="px-6 py-4 bg-surface-container-lowest border-t border-b border-white/5">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-on-surface-variant uppercase tracking-widest font-bold">Total
                        Keseluruhan</span>
                    <span id="payment_grand_total"
                        class="text-lg md:text-xl font-headline font-black text-primary">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] text-on-surface-variant/60">DP 50%</span>
                    <span id="payment_dp_amount" class="text-sm font-bold text-white/60">-</span>
                </div>
            </div>

            {{-- Pilihan pembayaran --}}
            <div class="p-6 flex flex-col gap-3">
                <p class="text-[10px] text-on-surface-variant uppercase tracking-widest font-bold text-center mb-1">
                    Pilih Metode Pembayaran
                </p>

                <button id="btn_pay_lunas" onclick="window.processPayment('lunas')"
                    class="w-full gold-gradient text-on-primary py-4 rounded-xl font-headline font-black uppercase text-sm tracking-[0.15em] hover:scale-[1.02] transition-transform shadow-[0_0_20px_rgba(242,202,80,0.3)] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">payments</span>
                    Bayar Lunas
                </button>

                <button id="btn_pay_dp" onclick="window.processPayment('dp')"
                    class="w-full bg-surface-container-high border border-primary/30 text-primary py-4 rounded-xl font-headline font-bold uppercase text-sm tracking-[0.15em] hover:bg-primary/10 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    Bayar DP 50%
                </button>

                <p class="text-[9px] text-on-surface-variant/50 text-center leading-relaxed">
                    Pembayaran diproses aman melalui Midtrans.<br>
                    Mendukung Transfer Bank, QRIS, GoPay, OVO, dll.
                </p>
            </div>
        </div>
    </div>

    {{-- Config untuk JS --}}
    <script>
        window.APP_CONFIG = {
            orderId: {{ $id }},
            csrfToken: "{{ csrf_token() }}",
            apiToken: sessionStorage.getItem('access_token'),
            apiUrl: "{{ url('/customer/api/progress-pesanan/' . $id) }}",
            markReadUrl: "{{ url('/customer/progress-pesanan/' . $id . '/chat/read') }}"
        };

        window.APP_CONFIG.currentUserId = (function() {
            if (!window.APP_CONFIG.apiToken) return 0;
            try {
                const payload = JSON.parse(atob(window.APP_CONFIG.apiToken.split('.')[1]));
                return payload.sub || 0;
            } catch (e) {
                return 0;
            }
        })();
    </script>
    <script>
        window.ORDER_ID = {{ $id }};
    </script>
    <script
        src="{{ asset('js/customer/orders/project-room.js') }}?v={{ filemtime(public_path('js/customer/orders/project-room.js')) }}">
    </script>

</body>

</html>
