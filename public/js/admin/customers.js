function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

var TOKEN = sessionStorage.getItem("access_token");
var currentTab = "aktif";
var editingId = null;
var allCustomers = [];
var searchQuery = "";

function loadCustomers() {
    setTableLoading();
    var url =
        window.customersApiUrl +
        (currentTab === "terhapus" ? "?trashed=1" : "");
    fetch(url, {
        headers: {
            Authorization: "Bearer " + TOKEN,
            Accept: "application/json",
        },
    })
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            allCustomers = d.customers;
            renderFilteredTable();
        })
        .catch(function (e) {
            console.error("[Customers]", e);
        });
}

function setTableLoading() {
    document.getElementById("customer_table_body").innerHTML =
        '<tr><td colspan="5" class="px-6 py-12 text-center">' +
        '<span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>' +
        "</td></tr>";
}

// filter data sesuai kata kunci pencarian lalu render
function renderFilteredTable() {
    if (currentTab === "aktif") {
        document.getElementById("stat_total").textContent = allCustomers.length;
    }

    var filtered = allCustomers;
    if (searchQuery) {
        var q = searchQuery.toLowerCase();
        filtered = allCustomers.filter(function (c) {
            return (
                c.nama_lengkap.toLowerCase().indexOf(q) !== -1 ||
                c.email.toLowerCase().indexOf(q) !== -1 ||
                c.no_whatsapp.toLowerCase().indexOf(q) !== -1
            );
        });
    }

    renderTable(filtered);
}

// dipanggil dari input search setiap kali user mengetik
function handleSearchInput(value) {
    searchQuery = value.trim();
    renderFilteredTable();
}

function renderTable(customers) {
    var tbody = document.getElementById("customer_table_body");

    if (!customers.length) {
        var emptyMsg = searchQuery
            ? "Tidak ditemukan pelanggan dengan kata kunci tersebut."
            : "Belum ada data pelanggan.";
        tbody.innerHTML =
            '<tr><td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">' +
            emptyMsg +
            "</td></tr>";
        return;
    }

    tbody.innerHTML = customers
        .map(function (c) {
            var initial = c.nama_lengkap.charAt(0).toUpperCase();
            var idStr = "#CUST-" + String(c.id).padStart(4, "0");
            var showUrl = window.customerShowUrl.replace("__ID__", c.id);
            var namaEscaped = c.nama_lengkap.replace(/'/g, "\\'");

            var actions =
                currentTab === "aktif"
                    ? '<div class="flex items-center justify-end gap-2">' +
                    '<a href="' +
                    showUrl +
                    '" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-primary hover:text-on-primary transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">visibility</span> Detail</a>' +
                    "<button onclick='openEditModal(" +
                    JSON.stringify(c) +
                    ')\' class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-blue-500/20 hover:border-blue-500/30 hover:text-blue-400 transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">edit</span> Edit</button>' +
                    '<button onclick="confirmDeleteCustomer(' +
                    c.id +
                    ",'" +
                    namaEscaped +
                    '\')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-red-500/20 hover:border-red-500/30 hover:text-red-400 transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">delete</span> Hapus</button></div>'
                    : '<div class="flex justify-end"><button onclick="confirmRestoreCustomer(' +
                    c.id +
                    ",'" +
                    namaEscaped +
                    '\')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-green-500/20 hover:border-green-500/30 hover:text-green-400 transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">restore</span> Pulihkan</button></div>';

            return (
                '<tr class="hover:bg-white/[0.02] transition-colors group">' +
                '<td class="px-6 py-5"><div class="flex items-center gap-4">' +
                '<div class="w-10 h-10 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold flex-shrink-0">' +
                escapeHtml(initial) +
                "</div>" +
                '<div><p class="text-white font-bold text-sm">' +
                escapeHtml(c.nama_lengkap) +
                "</p>" +
                '<p class="text-[10px] text-on-surface-variant uppercase tracking-widest">' +
                idStr +
                "</p></div></div></td>" +
                '<td class="px-6 py-5"><p class="text-white text-sm">' +
                escapeHtml(c.email) +
                '</p><p class="text-xs text-on-surface-variant">' +
                escapeHtml(c.no_whatsapp) +
                "</p></td>" +
                '<td class="px-6 py-5 text-center"><span class="inline-flex items-center px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-medium text-white">' +
                c.orders_count +
                " Pesanan</span></td>" +
                '<td class="px-6 py-5 text-sm text-on-surface-variant">' +
                c.tanggal_bergabung +
                "</td>" +
                '<td class="px-6 py-5">' +
                actions +
                "</td></tr>"
            );
        })
        .join("");
}

function switchTab(tab) {
    currentTab = tab;
    ["aktif", "terhapus"].forEach(function (t) {
        document.getElementById("tab_" + t).className =
            t === tab
                ? "px-5 py-2.5 text-xs font-bold rounded-xl transition-colors bg-primary text-background"
                : "px-5 py-2.5 text-xs font-bold rounded-xl transition-colors text-on-surface-variant hover:text-white hover:bg-white/5";
    });

    // reset pencarian setiap ganti tab
    searchQuery = "";
    document.getElementById("search_input").value = "";

    loadCustomers();
}

function openAddModal() {
    editingId = null;
    document.getElementById("modal_title").textContent = "Tambah Pelanggan";
    document.getElementById("customer_form").reset();
    var counterWa = document.getElementById("counter_wa");
    if (counterWa) counterWa.textContent = "0/13";
    document.getElementById("password_hint").textContent = "";
    document.getElementById("field_password").placeholder =
        "Minimal 8 karakter";
    document.getElementById("modal_overlay").classList.remove("hidden");
}

function openEditModal(c) {
    editingId = c.id;
    document.getElementById("modal_title").textContent = "Edit Data Pelanggan";
    document.getElementById("field_nama").value = c.nama_lengkap;
    document.getElementById("field_email").value = c.email;
    var inputWa = document.getElementById("field_wa");
    inputWa.value = c.no_whatsapp;
    inputWa.dispatchEvent(new Event('input'));
    document.getElementById("field_instansi").value =
        c.nama_instansi_brand !== "-" ? c.nama_instansi_brand : "";
    document.getElementById("field_alamat").value = "";
    document.getElementById("field_password").value = "";
    document.getElementById("field_password").placeholder =
        "Kosongkan jika tidak diubah";
    document.getElementById("password_hint").textContent =
        "* Kosongkan jika tidak ingin mengubah password";
    document.getElementById("modal_overlay").classList.remove("hidden");
}

function closeModal() {
    document.getElementById("modal_overlay").classList.add("hidden");
}

async function submitForm(e) {
    e.preventDefault();
    var btn = document.getElementById("btn_submit");
    btn.disabled = true;
    btn.textContent = "Menyimpan...";
    var body = {
        nama_lengkap: document.getElementById("field_nama").value,
        email: document.getElementById("field_email").value,
        password: document.getElementById("field_password").value,
        no_whatsapp: document.getElementById("field_wa").value,
        nama_instansi_brand: document.getElementById("field_instansi").value,
        alamat_pengiriman: document.getElementById("field_alamat").value,
    };
    var url = editingId
        ? window.customersUpdateUrl.replace("__ID__", editingId)
        : window.customersStoreUrl;
    var method = editingId ? "PUT" : "POST";
    try {
        var res = await fetch(url, {
            method,
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: JSON.stringify(body),
        });
        var data = await res.json();
        if (!res.ok) throw new Error(data.message || "Terjadi kesalahan.");
        closeModal();
        showToast(data.message, "green");
        loadCustomers();
    } catch (err) {
        showToast(err.message, "red");
    } finally {
        btn.disabled = false;
        btn.textContent = "Simpan";
    }
}

var pendingConfirmAction = null;

function confirmDeleteCustomer(id, nama) {
    var iconWrap = document.getElementById("confirm_icon_wrap");
    var icon = document.getElementById("confirm_icon");
    var actionBtn = document.getElementById("confirm_action_btn");

    iconWrap.className =
        "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-red-500/10 border-red-500/30 text-red-500";
    icon.textContent = "delete";
    document.getElementById("confirm_title").textContent = "Hapus Pelanggan?";
    document.getElementById("confirm_message").innerHTML =
        'Akun pelanggan <strong class="text-white">"' +
        nama +
        '"</strong> akan dipindahkan ke tab Terhapus. Anda bisa memulihkannya kapan saja.';
    actionBtn.className =
        "flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg bg-red-500 text-white hover:bg-red-600";
    actionBtn.textContent = "Ya, Hapus";

    pendingConfirmAction = function () {
        deleteCustomer(id);
    };

    document.getElementById("confirm_modal").classList.remove("hidden");
}

function confirmRestoreCustomer(id, nama) {
    var iconWrap = document.getElementById("confirm_icon_wrap");
    var icon = document.getElementById("confirm_icon");
    var actionBtn = document.getElementById("confirm_action_btn");

    iconWrap.className =
        "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-green-500/10 border-green-500/30 text-green-500";
    icon.textContent = "restore";
    document.getElementById("confirm_title").textContent = "Pulihkan Pelanggan?";
    document.getElementById("confirm_message").innerHTML =
        'Akun pelanggan <strong class="text-white">"' +
        nama +
        '"</strong> akan dikembalikan ke daftar Aktif.';
    actionBtn.className =
        "flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg bg-green-500 text-white hover:bg-green-600";
    actionBtn.textContent = "Ya, Pulihkan";

    pendingConfirmAction = function () {
        restoreCustomer(id);
    };

    document.getElementById("confirm_modal").classList.remove("hidden");
}

function closeConfirmModal() {
    document.getElementById("confirm_modal").classList.add("hidden");
    pendingConfirmAction = null;
}

function runConfirmedAction() {
    if (pendingConfirmAction) pendingConfirmAction();
    closeConfirmModal();
}

async function deleteCustomer(id) {
    try {
        var res = await fetch(
            window.customersDestroyUrl.replace("__ID__", id),
            {
                method: "DELETE",
                headers: {
                    Authorization: "Bearer " + TOKEN,
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
            },
        );
        var data = await res.json();
        if (!res.ok) throw new Error(data.message);
        showToast(data.message, "green");
        loadCustomers();
    } catch (e) {
        showToast(e.message || "Gagal menghapus.", "red");
    }
}

async function restoreCustomer(id) {
    try {
        var res = await fetch(
            window.customersRestoreUrl.replace("__ID__", id),
            {
                method: "POST",
                headers: {
                    Authorization: "Bearer " + TOKEN,
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
            },
        );
        var data = await res.json();
        showToast(data.message, "green");
        loadCustomers();
    } catch (e) {
        showToast("Gagal memulihkan.", "red");
    }
}

function showToast(msg, color) {
    var colors = {
        green: "bg-green-500/10 border-green-500/30 text-green-400",
        red: "bg-red-500/10 border-red-500/30 text-red-400",
    };
    var toast = document.createElement("div");
    toast.className =
        "px-5 py-3 rounded-xl border text-xs font-bold animate-slide-in " +
        (colors[color] || colors.green);
    toast.textContent = msg;
    document.getElementById("toast_container").appendChild(toast);
    setTimeout(function () {
        toast.remove();
    }, 3500);
}

document.addEventListener("DOMContentLoaded", loadCustomers);

document.addEventListener("DOMContentLoaded", function () {
    var searchEl = document.getElementById("search_input");
    if (searchEl) {
        searchEl.addEventListener("input", debounce(function () {
            handleSearchInput(this.value);
        }, 400));
    }
});