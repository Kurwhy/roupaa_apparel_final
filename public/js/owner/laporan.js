function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

(function () {
    var role = sessionStorage.getItem("user_role");
    if (role !== "owner") {
        window.location.href = "/ops/dashboard";
        return;
    }
})();

var TOKEN = sessionStorage.getItem("access_token");
var currentBulan = null;
var currentTahun = new Date().getFullYear();
var currentData = null;

function fmt(num) {
    return "Rp " + Number(num).toLocaleString("id-ID");
}

function statusBadge(status) {
    var map = {
        dp_paid: { cls: "bg-yellow-500/10 border-yellow-500/20 text-yellow-400", label: "DP Dibayar" },
        paid: { cls: "bg-green-500/10 border-green-500/20 text-green-400", label: "Lunas" },
    };
    var s = map[status] || { cls: "bg-white/5 text-white/50", label: status };
    return '<span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-medium ' + s.cls + '">' + s.label + '</span>';
}

function setLoading(id, cols) {
    document.getElementById(id).innerHTML =
        '<tr><td colspan="' + cols + '" class="px-6 py-12 text-center">' +
        '<span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>' +
        '</td></tr>';
}

// ── Filter ──────────────────────────────────────────────────────
function setFilterSemua() {
    currentBulan = null;
    document.getElementById('sel_bulan').value = '';
    updateFilterBtnStyle(true);
    loadLaporan();
}

function applyFilter() {
    currentBulan = document.getElementById('sel_bulan').value || null;
    currentTahun = document.getElementById('sel_tahun').value || new Date().getFullYear();
    updateFilterBtnStyle(!currentBulan);
    loadLaporan();
}

function updateFilterBtnStyle(isAll) {
    var btn = document.getElementById('btn_semua');
    if (isAll) {
        btn.style.cssText = 'background:var(--md-sys-color-primary,#F2CA50);color:#000;border-color:var(--md-sys-color-primary,#F2CA50)';
    } else {
        btn.style.cssText = 'background:transparent;color:rgba(255,255,255,0.5);border-color:rgba(255,255,255,0.1)';
    }
}

// ── Load data ───────────────────────────────────────────────────
async function loadLaporan() {
    setLoading("orders_table", 5);
    setLoading("restock_table", 5);

    var url = window.laporanApiUrl + '?tahun=' + currentTahun;
    if (currentBulan) url += '&bulan=' + currentBulan;

    try {
        var res = await fetch(url, { headers: { Authorization: "Bearer " + TOKEN } });
        var data = await res.json();
        currentData = data;

        document.getElementById('label_periode').textContent = 'Periode: ' + data.periode;
        document.getElementById("stat_pendapatan").textContent = fmt(data.total_pendapatan);
        document.getElementById("stat_pengeluaran").textContent = fmt(data.total_pengeluaran);
        document.getElementById("stat_saldo").textContent = fmt(data.saldo_bersih);
        document.getElementById("stat_transaksi").textContent = data.total_transaksi;

        var tbody = document.getElementById("orders_table");
        tbody.innerHTML = data.orders.length
            ? data.orders.map(function (o) {
                return '<tr class="hover:bg-white/[0.02] transition-colors">' +
                    '<td class="px-6 py-5"><p class="text-white font-bold text-sm font-mono">' + escapeHtml(o.order_number) + '</p>' +
                    '<p class="text-[10px] text-on-surface-variant mt-0.5">' + escapeHtml(o.project_name) + '</p></td>' +
                    '<td class="px-6 py-5 text-sm text-white">' + escapeHtml(o.pelanggan) + '</td>' +
                    '<td class="px-6 py-5">' + statusBadge(o.payment_status) + '</td>' +
                    '<td class="px-6 py-5 text-right"><p class="text-green-400 font-bold text-sm">' + fmt(o.jumlah_diterima) + '</p></td>' +
                    '<td class="px-6 py-5 text-right text-sm text-on-surface-variant">' + o.tanggal + '</td></tr>';
            }).join('')
            : '<tr><td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">Belum ada transaksi</td></tr>';

        var rtbody = document.getElementById("restock_table");
        rtbody.innerHTML = data.restocks.length
            ? data.restocks.map(function (r) {
                var strukCell = r.struk_url
                    ? '<button onclick="openStrukModal(\'' + r.struk_url + '\')" class="inline-flex items-center gap-1 text-primary hover:underline text-xs font-bold"><span class="material-symbols-outlined text-[14px]">receipt_long</span> Lihat</button>'
                    : '<span class="text-on-surface-variant/40 text-xs">-</span>';
                return '<tr class="hover:bg-white/[0.02] transition-colors">' +
                    '<td class="px-6 py-5 text-white font-bold text-sm font-mono">' + escapeHtml(r.invoice_number) + '</td>' +
                    '<td class="px-6 py-5 text-sm text-on-surface-variant">' + escapeHtml(r.diverifikasi_oleh) + '</td>' +
                    '<td class="px-6 py-5 text-right"><p class="text-red-400 font-bold text-sm">' + fmt(r.total_pengeluaran) + '</p></td>' +
                    '<td class="px-6 py-5 text-center">' + strukCell + '</td>' +
                    '<td class="px-6 py-5 text-right text-sm text-on-surface-variant">' + r.tanggal + '</td></tr>';
            }).join('')
            : '<tr><td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">Belum ada data pengeluaran</td></tr>';

    } catch (e) {
        console.error("[Laporan]", e);
    }
}

function printLaporan() {
    if (!currentData) return;
    var d = currentData;
    var saldoColor = d.saldo_bersih >= 0 ? '#16a34a' : '#dc2626';
    var tanggalCetak = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    var jamCetak = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    var ordersRows = d.orders.length
        ? d.orders.map(function (o, i) {
            return '<tr>' +
                '<td style="text-align:center">' + (i + 1) + '</td>' +
                '<td style="font-family:monospace;font-weight:700">' + escapeHtml(o.order_number) + '</td>' +
                '<td>' + escapeHtml(o.project_name || '-') + '</td>' +
                '<td>' + escapeHtml(o.pelanggan) + '</td>' +
                '<td style="text-align:center">' + (o.payment_status === 'paid' ? 'Lunas' : 'DP Dibayar') + '</td>' +
                '<td style="text-align:right;font-weight:700;color:#16a34a">' + fmt(o.jumlah_diterima) + '</td>' +
                '<td style="text-align:right">' + o.tanggal + '</td>' +
                '</tr>';
        }).join('')
        : '<tr><td colspan="7" style="text-align:center;color:#999;padding:16px">Tidak ada data pemasukan pada periode ini</td></tr>';

    var restocksRows = d.restocks.length
        ? d.restocks.map(function (r, i) {
            return '<tr>' +
                '<td style="text-align:center">' + (i + 1) + '</td>' +
                '<td style="font-family:monospace;font-weight:700">' + escapeHtml(r.invoice_number) + '</td>' +
                '<td>' + escapeHtml(r.diverifikasi_oleh) + '</td>' +
                '<td style="text-align:right;font-weight:700;color:#dc2626">' + fmt(r.total_pengeluaran) + '</td>' +
                '<td style="text-align:right">' + r.tanggal + '</td>' +
                '</tr>';
        }).join('')
        : '<tr><td colspan="5" style="text-align:center;color:#999;padding:16px">Tidak ada data pengeluaran pada periode ini</td></tr>';

    var html = '<!DOCTYPE html><html lang="id"><head>' +
        '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">' +
        '<title>Laporan Keuangan ROUPAA — ' + escapeHtml(d.periode) + '</title>' +
        '<link rel="preconnect" href="https://fonts.googleapis.com">' +
        '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">' +
        '<style>' +
        'body{font-family:"Inter",sans-serif;background:#fff;color:#000;margin:0;padding:0}' +

        '.no-print{background:#f3f4f6;padding:16px;margin-bottom:32px;display:flex;justify-content:flex-end;gap:12px;border-bottom:1px solid #d1d5db}' +
        '.no-print button{padding:8px 16px;border-radius:4px;font-size:13px;font-weight:700;cursor:pointer}' +
        '.btn-close{background:#fff;border:1px solid #d1d5db;color:#000}' +
        '.btn-print{background:#000;border:1px solid #000;color:#fff}' +

        '.wrap{max-width:800px;margin:0 auto;padding:0 16px 40px}' +

        '.kop{display:flex;justify-content:space-between;align-items:flex-end;border-bottom:2px solid #000;padding-bottom:16px;margin-bottom:24px}' +
        '.kop-left h1{font-size:22px;font-weight:900;text-transform:uppercase;letter-spacing:3px;margin:0}' +
        '.kop-left p{font-size:11px;color:#555;margin:4px 0 0}' +
        '.kop-right{text-align:right}' +
        '.doc-type{font-size:17px;font-weight:700;text-transform:uppercase;letter-spacing:2px;background:#000;color:#fff;padding:4px 12px;display:inline-block}' +
        '.doc-date{font-size:13px;font-weight:600;margin-top:8px}' +

        '.info{font-size:13px;margin-bottom:16px}' +
        '.info p{margin:2px 0}' +

        '.summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:24px}' +
        '.sum-box{border:1px solid #000;padding:10px 12px;border-radius:4px}' +
        '.sum-label{font-size:9px;text-transform:uppercase;letter-spacing:1px;color:#666;margin-bottom:4px}' +
        '.sum-val{font-size:14px;font-weight:900}' +

        'h2{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin:20px 0 8px;color:#444;border-bottom:1px solid #000;padding-bottom:4px}' +
        'table{width:100%;border-collapse:collapse;margin-top:0}' +
        'th,td{border:1px solid #000;padding:8px 10px;text-align:left;font-size:12px}' +
        'th{background-color:#f3f4f6}' +
        'tr:nth-child(even) td{background:#fafafa}' +

        '.footer{margin-top:24px;font-size:10px;color:#888;text-align:center;border-top:1px solid #d1d5db;padding-top:12px}' +

        '@media print{' +
        '@page{size:A4;margin:15mm}' +
        'body{-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
        '.no-print{display:none!important}' +
        '.wrap{padding:0}' +
        '}' +
        '</style></head>' +

        '<body>' +

        '<div class="no-print">' +
        '<button class="btn-close" onclick="window.close()">Tutup</button>' +
        '<button class="btn-print" onclick="window.print()">Cetak Kertas</button>' +
        '</div>' +

        '<div class="wrap">' +

        '<div class="kop">' +
        '<div class="kop-left">' +
        '<h1>ROUPAA APPAREL</h1>' +
        '<p>Jl. Godean KM 5, Yogyakarta, Indonesia | Telp: 0812-XXXX-XXXX</p>' +
        '</div>' +
        '<div class="kop-right">' +
        '<div class="doc-type">LAPORAN KEUANGAN</div>' +
        '<p class="doc-date">TGL: ' + tanggalCetak + '</p>' +
        '</div>' +
        '</div>' +

        '<div class="info">' +
        '<p><strong>Periode:</strong> ' + escapeHtml(d.periode) + '</p>' +
        '<p><strong>Dicetak Pada:</strong> ' + tanggalCetak + ', ' + jamCetak + '</p>' +
        '</div>' +

        '<div class="summary">' +
        '<div class="sum-box"><div class="sum-label">Total Pemasukan</div><div class="sum-val" style="color:#16a34a">' + fmt(d.total_pendapatan) + '</div></div>' +
        '<div class="sum-box"><div class="sum-label">Total Pengeluaran</div><div class="sum-val" style="color:#dc2626">' + fmt(d.total_pengeluaran) + '</div></div>' +
        '<div class="sum-box"><div class="sum-label">Saldo Bersih</div><div class="sum-val" style="color:' + saldoColor + '">' + fmt(d.saldo_bersih) + '</div></div>' +
        '<div class="sum-box"><div class="sum-label">Total Transaksi</div><div class="sum-val">' + d.total_transaksi + '</div></div>' +
        '</div>' +

        '<h2>Riwayat Pemasukan — Pembayaran Pesanan</h2>' +
        '<table><thead><tr>' +
        '<th style="width:4%;text-align:center">No</th>' +
        '<th style="width:18%">No. Pesanan</th>' +
        '<th style="width:22%">Nama Proyek</th>' +
        '<th style="width:20%">Pelanggan</th>' +
        '<th style="width:10%;text-align:center">Status</th>' +
        '<th style="width:14%;text-align:right">Jumlah Diterima</th>' +
        '<th style="width:12%;text-align:right">Tanggal</th>' +
        '</tr></thead><tbody>' + ordersRows + '</tbody></table>' +

        '<h2>Riwayat Pengeluaran — Belanja Bahan Apparel</h2>' +
        '<table><thead><tr>' +
        '<th style="width:4%;text-align:center">No</th>' +
        '<th style="width:28%">No. Invoice</th>' +
        '<th style="width:32%">Diverifikasi Oleh</th>' +
        '<th style="width:20%;text-align:right">Jumlah Dikeluarkan</th>' +
        '<th style="width:16%;text-align:right">Tanggal</th>' +
        '</tr></thead><tbody>' + restocksRows + '</tbody></table>' +

        '<div class="footer">' +
        'Dokumen ini di-generate otomatis oleh Sistem ROUPAA pada ' +
        tanggalCetak + ' ' + jamCetak +
        '</div>' +

        '</div>' + // .wrap
        '</body></html>';

    var win = window.open('', '_blank', 'width=950,height=750');
    win.document.write(html);
    win.document.close();
    win.focus();
}

function openStrukModal(url) {
    var modal = document.getElementById("strukModal");
    var finalUrl = url.startsWith('http') ? url : '/storage/' + url;
    document.getElementById("struk_image").src = finalUrl;
    document.getElementById("btn_download_struk").href = finalUrl;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeStrukModal() {
    var modal = document.getElementById("strukModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

document.addEventListener("DOMContentLoaded", function () {
    var selTahun = document.getElementById('sel_tahun');
    if (selTahun) currentTahun = selTahun.value || new Date().getFullYear();
    loadLaporan();
});