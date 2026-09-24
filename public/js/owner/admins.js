(function () {
    var role = sessionStorage.getItem("user_role");
    if (role !== "owner") {
        window.location.href = "/ops/dashboard";
        return;
    }
})();

var TOKEN = sessionStorage.getItem("access_token");
var currentTab = "aktif";
var editingId = null;
var allAdmins = [];
var searchQuery = "";

function loadAdmins() {
    setTableLoading();
    var url =
        window.adminsApiUrl + (currentTab === "terhapus" ? "?trashed=1" : "");
    fetch(url, { headers: { Authorization: "Bearer " + TOKEN } })
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            allAdmins = d.admins;
            renderFilteredTable();
        })
        .catch(function (e) {
            console.error("[Admins]", e);
        });
}

function setTableLoading() {
    document.getElementById("admin_table_body").innerHTML =
        '<tr><td colspan="6" class="px-6 py-12 text-center">' +
        '<span class="material-symbols-outlined text-2xl text-white/10 animate-spin">sync</span>' +
        "</td></tr>";
}

// filter data sesuai kata kunci pencarian lalu render
function renderFilteredTable() {
    if (currentTab === "aktif") {
        document.getElementById("stat_total_admin").textContent = allAdmins.length;
    }

    var filtered = allAdmins;
    if (searchQuery) {
        var q = searchQuery.toLowerCase();
        filtered = allAdmins.filter(function (a) {
            return (
                a.nama_lengkap.toLowerCase().indexOf(q) !== -1 ||
                a.email.toLowerCase().indexOf(q) !== -1 ||
                a.no_telepon.toLowerCase().indexOf(q) !== -1 ||
                a.divisi.toLowerCase().indexOf(q) !== -1 ||
                a.nip_karyawan.toLowerCase().indexOf(q) !== -1
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

function renderTable(admins) {
    var tbody = document.getElementById("admin_table_body");

    if (!admins.length) {
        var emptyMsg = searchQuery
            ? "Tidak ditemukan admin dengan kata kunci tersebut."
            : "Tidak ada data";
        tbody.innerHTML =
            '<tr><td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">' +
            emptyMsg +
            "</td></tr>";
        return;
    }

    tbody.innerHTML = admins
        .map(function (a) {
            var initial = a.nama_lengkap.charAt(0).toUpperCase();
            var idStr = "#ADM-" + String(a.id).padStart(4, "0");
            var namaEscaped = a.nama_lengkap.replace(/'/g, "\\'");

            var actions =
                currentTab === "aktif"
                    ? '<div class="flex items-center justify-end gap-2">' +
                    "<button onclick='openEditModal(" +
                    JSON.stringify(a) +
                    ")' " +
                    'class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-blue-500/20 hover:border-blue-500/30 hover:text-blue-400 transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">edit</span> Edit</button>' +
                    '<button onclick="confirmDeleteAdmin(' +
                    a.id +
                    ",'" +
                    namaEscaped +
                    '\')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-red-500/20 hover:border-red-500/30 hover:text-red-400 transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">delete</span> Hapus</button>' +
                    "</div>"
                    : '<div class="flex justify-end">' +
                    '<button onclick="confirmRestoreAdmin(' +
                    a.id +
                    ",'" +
                    namaEscaped +
                    '\')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-green-500/20 hover:border-green-500/30 hover:text-green-400 transition-all shadow-md">' +
                    '<span class="material-symbols-outlined text-[14px]">restore</span> Pulihkan</button>' +
                    "</div>";

            return (
                '<tr class="hover:bg-white/[0.02] transition-colors group">' +
                '<td class="px-6 py-5">' +
                '<div class="flex items-center gap-4">' +
                '<div class="w-10 h-10 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold flex-shrink-0">' +
                initial +
                "</div>" +
                '<div><p class="text-white font-bold text-sm">' +
                a.nama_lengkap +
                "</p>" +
                '<p class="text-[10px] text-on-surface-variant uppercase tracking-widest">' +
                idStr +
                "</p></div>" +
                "</div></td>" +
                '<td class="px-6 py-5">' +
                '<p class="text-white text-sm">' +
                a.email +
                "</p>" +
                '<p class="text-xs text-on-surface-variant">' +
                a.no_telepon +
                "</p>" +
                "</td>" +
                '<td class="px-6 py-5">' +
                '<span class="inline-flex items-center px-3 py-1 rounded-full bg-primary/10 border border-primary/20 text-xs font-medium text-primary">' +
                a.divisi +
                "</span>" +
                "</td>" +
                '<td class="px-6 py-5 text-sm text-on-surface-variant">' +
                a.nip_karyawan +
                "</td>" +
                '<td class="px-6 py-5 text-sm text-on-surface-variant">' +
                a.tanggal_bergabung +
                "</td>" +
                '<td class="px-6 py-5">' +
                actions +
                "</td>" +
                "</tr>"
            );
        })
        .join("");
}

function switchTab(tab) {
    currentTab = tab;
    ["aktif", "terhapus"].forEach(function (t) {
        var el = document.getElementById("tab_" + t);
        el.className =
            t === tab
                ? "px-5 py-2.5 text-xs font-bold rounded-xl transition-colors bg-primary text-background"
                : "px-5 py-2.5 text-xs font-bold rounded-xl transition-colors text-on-surface-variant hover:text-white hover:bg-white/5";
    });
    document.getElementById("btn_tambah").style.display =
        tab === "aktif" ? "" : "none";

    // reset pencarian setiap ganti tab
    searchQuery = "";
    document.getElementById("search_input").value = "";

    loadAdmins();
}

function openAddModal() {
    editingId = null;
    document.getElementById("modal_title").textContent = "Tambah Akun Admin";
    document.getElementById("admin_form").reset();
    var counterTelepon = document.getElementById("counter_no_telepon");
    if (counterTelepon) counterTelepon.textContent = "0/13";
    document.getElementById("password_hint").textContent = "";
    document.getElementById("field_password").placeholder =
        "Minimal 8 karakter";
    document.getElementById("modal_overlay").classList.remove("hidden");
}

function openEditModal(admin) {
    editingId = admin.id;
    document.getElementById("modal_title").textContent = "Edit Akun Admin";
    document.getElementById("field_nama").value = admin.nama_lengkap;
    document.getElementById("field_email").value = admin.email;
    var inputTelepon = document.getElementById("field_no_telepon");
    inputTelepon.value = admin.no_telepon;
    inputTelepon.dispatchEvent(new Event('input'));
    document.getElementById("field_divisi").value = admin.divisi;
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

    var nama = document.getElementById("field_nama").value.trim();
    var email = document.getElementById("field_email").value.trim();
    var telepon = document.getElementById("field_no_telepon").value.trim();
    var divisi = document.getElementById("field_divisi").value.trim();
    var pass = document.getElementById("field_password").value;

    if (!nama || nama.length < 3) {
        showToast("Nama minimal 3 karakter.", "red");
        return;
    }
    if (!/^[a-zA-Z\s]+$/.test(nama)) {
        showToast("Nama hanya boleh huruf dan spasi.", "red");
        return;
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showToast("Format email tidak valid.", "red");
        return;
    }
    if (!telepon || !/^\d{10,13}$/.test(telepon)) {
        showToast("Nomor telepon harus 10-13 digit angka.", "red");
        return;
    }
    if (!divisi) {
        showToast("Divisi wajib diisi.", "red");
        return;
    }
    if (!editingId && !pass) {
        showToast("Password wajib diisi.", "red");
        return;
    }
    if (pass) {
        if (pass.length < 8) {
            showToast("Password minimal 8 karakter.", "red");
            return;
        }
        if (!/[A-Z]/.test(pass)) {
            showToast(
                "Password harus mengandung minimal satu huruf besar.",
                "red",
            );
            return;
        }
        if (!/[0-9]/.test(pass)) {
            showToast("Password harus mengandung minimal satu angka.", "red");
            return;
        }
    }

    var btn = document.getElementById("btn_submit");
    btn.disabled = true;
    btn.textContent = "Menyimpan...";

    var body = {
        nama_lengkap: nama,
        email: email,
        no_telepon: telepon,
        divisi: divisi,
        password: pass,
    };

    var url = editingId
        ? window.adminsUpdateUrl.replace("__ID__", editingId)
        : window.adminsStoreUrl;
    var method = editingId ? "PUT" : "POST";

    try {
        var res = await fetch(url, {
            method: method,
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
        loadAdmins();
    } catch (err) {
        showToast(err.message, "red");
    } finally {
        btn.disabled = false;
        btn.textContent = "Simpan";
    }
}

var pendingConfirmAction = null;

function confirmDeleteAdmin(id, nama) {
    var iconWrap = document.getElementById("confirm_icon_wrap");
    var icon = document.getElementById("confirm_icon");
    var actionBtn = document.getElementById("confirm_action_btn");

    iconWrap.className =
        "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-red-500/10 border-red-500/30 text-red-500";
    icon.textContent = "delete";
    document.getElementById("confirm_title").textContent = "Hapus Akun Admin?";
    document.getElementById("confirm_message").innerHTML =
        'Akun admin <strong class="text-white">"' +
        nama +
        '"</strong> akan dipindahkan ke tab Terhapus. Anda bisa memulihkannya kapan saja.';
    actionBtn.className =
        "flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg bg-red-500 text-white hover:bg-red-600";
    actionBtn.textContent = "Ya, Hapus";

    pendingConfirmAction = function () {
        deleteAdmin(id);
    };

    document.getElementById("confirm_modal").classList.remove("hidden");
}

function confirmRestoreAdmin(id, nama) {
    var iconWrap = document.getElementById("confirm_icon_wrap");
    var icon = document.getElementById("confirm_icon");
    var actionBtn = document.getElementById("confirm_action_btn");

    iconWrap.className =
        "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-green-500/10 border-green-500/30 text-green-500";
    icon.textContent = "restore";
    document.getElementById("confirm_title").textContent = "Pulihkan Akun Admin?";
    document.getElementById("confirm_message").innerHTML =
        'Akun admin <strong class="text-white">"' +
        nama +
        '"</strong> akan dikembalikan ke daftar Aktif.';
    actionBtn.className =
        "flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg bg-green-500 text-white hover:bg-green-600";
    actionBtn.textContent = "Ya, Pulihkan";

    pendingConfirmAction = function () {
        restoreAdmin(id);
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

async function deleteAdmin(id) {
    try {
        var res = await fetch(window.adminsDestroyUrl.replace("__ID__", id), {
            method: "DELETE",
            headers: {
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
        });
        var data = await res.json();
        showToast(data.message, "green");
        loadAdmins();
    } catch (e) {
        showToast("Gagal menghapus.", "red");
    }
}

async function restoreAdmin(id) {
    try {
        var res = await fetch(window.adminsRestoreUrl.replace("__ID__", id), {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
        });
        var data = await res.json();
        showToast(data.message, "green");
        loadAdmins();
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

document.addEventListener("DOMContentLoaded", loadAdmins);

document.addEventListener("DOMContentLoaded", function () {
    var searchEl = document.getElementById("search_input");
    if (searchEl) {
        searchEl.addEventListener("input", debounce(function () {
            handleSearchInput(this.value);
        }, 400));
    }
});