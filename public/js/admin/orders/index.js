var TOKEN = sessionStorage.getItem("access_token");
var currentTab = "all";
var searchQuery = "";
var allOrders = [];

// kelompok status untuk tab filter
var statusGroups = {
    diskusi: ["diskusi_desain", "menunggu_spesifikasi", "menunggu_estimasi"],
    menunggu_bayar: ["menunggu_pembayaran"],
    diproses: ["diproses"],
    selesai: ["siap_diambil", "selesai"],
};

// detail badge per status asli (untuk tampilan baris, terpisah dari tab)
var statusBadgeMap = {
    diskusi_desain: {
        label: "Diskusi Desain",
        icon: "design_services",
        bg: "bg-blue-500/10",
        text: "text-blue-400",
        border: "border-blue-500/20",
        pulse: true,
    },
    menunggu_spesifikasi: {
        label: "Menunggu Spesifikasi",
        icon: "format_list_bulleted",
        bg: "bg-cyan-500/10",
        text: "text-cyan-400",
        border: "border-cyan-500/20",
    },
    menunggu_estimasi: {
        label: "Menunggu Konfirmasi",
        icon: "fact_check",
        bg: "bg-yellow-500/10",
        text: "text-yellow-400",
        border: "border-yellow-500/20",
    },
    menunggu_pembayaran: {
        label: "Menunggu Pembayaran",
        icon: "payments",
        bg: "bg-orange-500/10",
        text: "text-orange-400",
        border: "border-orange-500/20",
    },
    diproses: {
        label: "Diproses",
        icon: "precision_manufacturing",
        bg: "bg-purple-500/10",
        text: "text-purple-400",
        border: "border-purple-500/20",
    },
    siap_diambil: {
        label: "Siap Diambil",
        icon: "inventory_2",
        bg: "bg-teal-500/10",
        text: "text-teal-400",
        border: "border-teal-500/20",
    },
    selesai: {
        label: "Selesai",
        icon: "check_circle",
        bg: "bg-green-500/10",
        text: "text-green-400",
        border: "border-green-500/20",
    },
};

function loadOrders() {
    setTableLoading();
    fetch(window.ordersApiUrl, {
        headers: {
            Authorization: "Bearer " + TOKEN,
            Accept: "application/json",
        },
    })
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            allOrders = d.orders;
            document.getElementById("total_count").textContent = allOrders.length;
            renderFilteredTable();
        })
        .catch(function (e) {
            console.error("[Orders]", e);
        });
}

function setTableLoading() {
    document.getElementById("orders_table_body").innerHTML =
        '<tr><td colspan="5" class="px-8 py-20 text-center">' +
        '<span class="material-symbols-outlined text-3xl text-white/10">sync</span>' +
        "</td></tr>";
}

// gabungkan filter tab status + pencarian
function renderFilteredTable() {
    var filtered = allOrders;

    if (currentTab !== "all") {
        var allowedStatuses = statusGroups[currentTab] || [];
        filtered = filtered.filter(function (o) {
            return allowedStatuses.indexOf(o.status) !== -1;
        });
    }

    if (searchQuery) {
        var q = searchQuery.toLowerCase();
        filtered = filtered.filter(function (o) {
            return (
                o.order_number.toLowerCase().indexOf(q) !== -1 ||
                o.project_name.toLowerCase().indexOf(q) !== -1 ||
                o.pelanggan.toLowerCase().indexOf(q) !== -1 ||
                o.instansi.toLowerCase().indexOf(q) !== -1
            );
        });
    }

    renderTable(filtered);
}

function handleSearchInput(value) {
    searchQuery = value.trim();
    renderFilteredTable();
}

function switchTab(tab) {
    currentTab = tab;
    ["all", "diskusi", "menunggu_bayar", "diproses", "selesai"].forEach(function (t) {
        var el = document.getElementById("tab_" + t);
        el.className =
            t === tab
                ? "whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-primary text-on-primary shadow-[0_0_15px_rgba(242,202,80,0.4)]"
                : "whitespace-nowrap flex-shrink-0 px-6 py-2 rounded-full text-[11px] font-bold uppercase tracking-widest transition-all bg-surface-container-high border border-white/5 text-on-surface-variant hover:text-white";
    });
    renderFilteredTable();
}

function renderTable(orders) {
    var tbody = document.getElementById("orders_table_body");

    if (!orders.length) {
        var emptyMsg = searchQuery
            ? "Tidak ditemukan pesanan dengan kata kunci tersebut."
            : "Belum ada data pesanan.";
        tbody.innerHTML =
            '<tr><td colspan="5" class="px-8 py-20 text-center">' +
            '<div class="flex flex-col items-center opacity-30">' +
            '<span class="material-symbols-outlined text-6xl mb-4">inventory_2</span>' +
            '<p class="font-headline font-bold uppercase tracking-widest">' +
            emptyMsg +
            "</p></div></td></tr>";
        return;
    }

    tbody.innerHTML = orders
        .map(function (o) {
            var s = statusBadgeMap[o.status] || {
                label: "Tidak Diketahui",
                icon: "help",
                bg: "bg-white/5",
                text: "text-on-surface-variant",
                border: "border-white/10",
            };

            var badgeIcon = s.pulse
                ? '<span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>'
                : '<span class="material-symbols-outlined text-[14px]' +
                '">' +
                s.icon +
                "</span>";

            var showUrl = window.orderShowUrlBase.replace("__ID__", o.id);

            return (
                '<tr class="hover:bg-white/[0.02] transition-colors group">' +
                '<td class="px-8 py-6">' +
                '<span class="text-primary font-headline font-black text-base block mb-1">#' +
                o.order_number +
                "</span>" +
                '<span class="text-[10px] text-on-surface-variant flex items-center gap-1.5 font-medium uppercase tracking-wider">' +
                '<span class="material-symbols-outlined text-[14px]">calendar_today</span>' +
                o.tanggal +
                "</span></td>" +
                '<td class="px-8 py-6">' +
                '<span class="text-white font-bold block text-sm">' +
                o.pelanggan +
                "</span>" +
                '<span class="text-[10px] text-on-surface-variant uppercase font-semibold tracking-wide">' +
                o.instansi +
                "</span></td>" +
                '<td class="px-8 py-6">' +
                '<span class="text-white/80 text-sm font-medium">' +
                o.project_name +
                "</span>" +
                '<span class="block text-[10px] text-on-surface-variant mt-1 italic">' +
                o.product_name +
                "</span></td>" +
                '<td class="px-8 py-6">' +
                '<div class="inline-flex items-center gap-2 ' +
                s.bg +
                " " +
                s.text +
                " border " +
                s.border +
                ' px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">' +
                badgeIcon +
                " " +
                s.label +
                "</div></td>" +
                '<td class="px-8 py-6 text-right">' +
                '<a href="' +
                showUrl +
                '" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-surface-container-high border border-white/10 text-white hover:bg-primary hover:text-on-primary transition-all shadow-lg group-hover:scale-110">' +
                '<span class="material-symbols-outlined text-[20px]">arrow_forward</span></a></td>' +
                "</tr>"
            );
        })
        .join("");
}

document.addEventListener("DOMContentLoaded", loadOrders);

document.addEventListener("DOMContentLoaded", function () {
    var searchEl = document.getElementById("search_input");
    if (searchEl) {
        searchEl.addEventListener("input", debounce(function () {
            handleSearchInput(this.value);
        }, 400));
    }
});