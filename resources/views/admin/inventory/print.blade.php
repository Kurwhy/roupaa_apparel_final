<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Pembelian (Restock)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: white;
            color: black;
        }

        /* CSS Khusus Printer */
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #f3f4f6 !important;
        }
    </style>
</head>

<body onload="window.print()">

    {{-- Tombol Print Manual (Sembunyi saat diprint) --}}
    <div class="no-print bg-gray-100 p-4 mb-8 flex justify-end gap-4 border-b border-gray-300">
        <button onclick="window.close()"
            class="px-4 py-2 bg-white border border-gray-300 rounded text-sm font-bold">Tutup</button>
        <button onclick="window.print()" class="px-4 py-2 bg-black text-white rounded text-sm font-bold">Cetak
            Kertas</button>
    </div>

    {{-- KOP SURAT --}}
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex justify-between items-end border-b-2 border-black pb-4 mb-6">
            <div>
                <h1 class="text-2xl font-black uppercase tracking-widest">ROUPAA APPAREL</h1>
                <p class="text-xs mt-1 text-gray-600">Jl. Godean KM 5, Yogyakarta, Indonesia | Telp: 0812-XXXX-XXXX</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold uppercase tracking-widest bg-black text-white px-3 py-1 inline-block">FORM
                    DAFTAR BELANJA</h2>
                <p class="text-sm mt-2 font-semibold">TGL: {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            </div>
        </div>

        {{-- INFO --}}
        @php
            $authUser = auth('api')->user();
            $namaPencetak = $authUser?->admin?->nama_lengkap ?? $authUser?->owner?->nama_lengkap ?? $authUser?->role ?? '-';
        @endphp
        <div class="mb-4 text-sm">
            <p><strong>Perihal:</strong> Pengajuan Pembelian Barang (Restock Inventori & Bahan Baku)</p>
            <p><strong>Dicetak Oleh:</strong> {{ $namaPencetak }}</p>
        </div>

        {{-- TABEL BARANG --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align:center;">No</th>
                    <th style="width: 45%;">Nama Barang / Deskripsi</th>
                    <th style="width: 15%; text-align:center;">Sisa Stok Gudang</th>
                    <th style="width: 15%; text-align:center;">Jumlah Dibeli</th>
                    <th style="width: 20%; text-align:center;">Keterangan / Check</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    {{-- Lewati jika nama barang kosong (baris kosong) --}}
                    @if(!empty($item['name']))
                        <tr>
                            <td style="text-align:center;">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $item['name'] }}</td>
                            <td style="text-align:center;">{{ $item['current_stock'] }} {{ $item['unit'] }}</td>
                            <td style="text-align:center; font-weight:bold; font-size: 16px;">{{ $item['qty'] }}
                                {{ $item['unit'] }}</td>
                            <td></td> {{-- Kotak kosong untuk dicentang pakai pulpen saat belanja --}}
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        {{-- KOLOM TANDA TANGAN --}}
        <div class="mt-16 flex justify-between px-10 text-center text-sm">
            <div class="flex flex-col items-center">
                <p class="mb-20">Disiapkan Oleh, (Admin Gudang)</p>
                <div class="border-b border-black w-48"></div>
                <p class="mt-2 font-bold">{{ $namaPencetak }}</p>
            </div>
            <div class="flex flex-col items-center">
                <p class="mb-20">Disetujui Oleh, (Owner / Keuangan)</p>
                <div class="border-b border-black w-48"></div>
                <p class="mt-2 font-bold">.........................</p>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="mt-20 text-xs text-gray-500 text-center border-t border-gray-300 pt-4">
            Dokumen ini di-generate otomatis oleh Sistem ROUPAA pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>

</html>