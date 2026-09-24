@extends('layouts.admin')

@section('content')
    <div class="p-4 md:p-10 space-y-6 md:space-y-8">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('ops.customers.index') }}"
                    class="w-10 h-10 rounded-full bg-surface-container-high border border-white/10 flex items-center justify-center text-white hover:bg-primary hover:text-on-primary transition-colors shadow-lg">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-headline text-2xl font-black text-white uppercase tracking-tight">Detail Pelanggan</h1>
                    <p class="text-on-surface-variant text-xs">ID: #CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1 space-y-6">
                <div
                    class="bg-surface-container-low border border-white/5 p-6 rounded-3xl shadow-xl text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-primary/20 to-transparent"></div>

                    <div class="relative z-10">
                        <div
                            class="w-24 h-24 mx-auto bg-surface border-4 border-primary/20 rounded-full flex items-center justify-center text-primary text-4xl font-black mb-4 shadow-lg">
                            {{ strtoupper(substr($customer->nama_lengkap ?? 'U', 0, 1)) }}
                        </div>
                        <h2 class="text-xl font-bold text-white mb-1">{{ $customer->nama_lengkap }}</h2>
                        <p class="text-xs text-on-surface-variant mb-6">Bergabung sejak
                            {{ $customer->created_at->translatedFormat('d F Y') }}</p>

                        <div class="flex flex-col sm:flex-row justify-center gap-3 w-full">
                            @if ($customer->no_whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->no_whatsapp) }}"
                                    target="_blank"
                                    class="flex-1 bg-[#25D366]/10 border border-[#25D366]/30 text-[#25D366] py-2 rounded-xl text-xs font-bold hover:bg-[#25D366] hover:text-white transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">chat</span> WhatsApp
                                </a>
                            @endif
                            <a href="mailto:{{ $customer->user->email }}"
                                class="flex-1 bg-surface-container-highest border border-white/10 text-white py-2 rounded-xl text-xs font-bold hover:bg-white/10 transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">mail</span> Email
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest border border-white/5 p-6 rounded-3xl shadow-lg">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-[0.2em] mb-4">Informasi Kontak</h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] text-on-surface-variant uppercase tracking-widest mb-1">Email Akun</p>
                            <p class="text-sm text-white font-medium">{{ $customer->user->email ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-on-surface-variant uppercase tracking-widest mb-1">Nomor Telepon/WA
                            </p>
                            <p class="text-sm text-white font-medium">{{ $customer->no_whatsapp ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-on-surface-variant uppercase tracking-widest mb-1">Alamat Pengiriman
                            </p>
                            <p class="text-sm text-white font-medium leading-relaxed">
                                {{ $customer->alamat_pengiriman ?? 'Belum ada alamat tersimpan.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div
                    class="bg-surface-container-lowest border border-white/5 rounded-3xl shadow-xl overflow-hidden h-full flex flex-col">
                    <div class="p-6 border-b border-white/5 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-white uppercase tracking-widest">Riwayat Pesanan
                            ({{ $customer->orders->count() ?? 0 }})</h3>
                    </div>

                    <div class="flex-grow p-6">
                        @if (empty($customer->orders) || $customer->orders->isEmpty())
                            <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-10">
                                <span
                                    class="material-symbols-outlined text-6xl text-on-surface-variant mb-4">shopping_cart_checkout</span>
                                <p class="text-white font-bold">Belum Ada Pesanan</p>
                                <p class="text-xs text-on-surface-variant">Pelanggan ini belum pernah membuat order.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($customer->orders as $order)
                                    <div
                                        class="bg-surface-container-low border border-white/5 p-4 rounded-2xl flex items-center justify-between hover:bg-white/5 transition-colors group shadow-sm">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-12 h-12 rounded-xl bg-surface-container-highest flex items-center justify-center border border-white/10">
                                                <span
                                                    class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">receipt</span>
                                            </div>
                                            <div>
                                                <p class="text-white font-bold text-sm mb-1">Order
                                                    #ORD-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</p>
                                                <p class="text-[10px] text-on-surface-variant">
                                                    {{ $order->created_at->format('d M Y, H:i') }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-6">
                                            <div class="text-right hidden md:block">
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase tracking-widest mb-1">
                                                    Status
                                                </p>
                                                <span
                                                    class="text-xs font-bold text-primary">{{ strtoupper($order->status ?? 'Pending') }}</span>
                                            </div>
                                            <a href="{{ route('ops.orders.show', $order->id) }}"
                                                class="w-8 h-8 rounded-full bg-surface-container-highest border border-white/10 flex items-center justify-center text-on-surface-variant hover:text-white hover:border-white/30 transition-all">
                                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
