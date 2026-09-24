var TOKEN = sessionStorage.getItem("access_token");
var currentTab = "aktif";

function loadPhotos() {
    setGridLoading();
    var url =
        window.portofolioApiUrl +
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
            renderGrid(d.photos);
        })
        .catch(function (e) {
            console.error("[Portofolio]", e);
        });
}

function setGridLoading() {
    document.getElementById("photo_grid").innerHTML =
        '<div class="col-span-full flex justify-center py-16">' +
        '<span class="material-symbols-outlined text-3xl text-white/10 animate-spin">sync</span></div>';
}

function renderGrid(photos) {
    var grid = document.getElementById("photo_grid");

    if (currentTab === "aktif") {
        document.getElementById("stat_total").textContent = photos.length;
    }

    if (!photos.length) {
        grid.innerHTML =
            '<div class="col-span-full py-16 text-center text-on-surface-variant">Belum ada foto portofolio.</div>';
        return;
    }

    grid.innerHTML = photos
        .map(function (p) {
            var actions =
                currentTab === "aktif"
                    ? '<div class="flex flex-col 2xl:flex-row gap-2">' +
                    "<button onclick='openEditModal(" +
                    JSON.stringify(p) +
                    ")' " +
                    'class="flex-1 flex items-center justify-center gap-1 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-blue-500/20 hover:border-blue-500/30 hover:text-blue-400 transition-all">' +
                    '<span class="material-symbols-outlined text-[14px]">edit</span> Edit</button>' +
                    '<button onclick="confirmDeletePhoto(' +
                    p.id +
                    ')" ' +
                    'class="flex-1 flex items-center justify-center gap-1 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-red-500/20 hover:border-red-500/30 hover:text-red-400 transition-all">' +
                    '<span class="material-symbols-outlined text-[14px]">delete</span> Hapus</button>' +
                    "</div>"
                    : '<button onclick="confirmRestorePhoto(' +
                    p.id +
                    ')" ' +
                    'class="w-full flex items-center justify-center gap-1 py-2 rounded-xl bg-surface-container-high border border-white/10 text-xs font-bold text-white hover:bg-green-500/20 hover:border-green-500/30 hover:text-green-400 transition-all">' +
                    '<span class="material-symbols-outlined text-[14px]">restore</span> Pulihkan</button>';

            return (
                '<div class="bg-surface-container-lowest border border-white/10 rounded-2xl overflow-hidden shadow-lg group">' +
                '<div class="aspect-square overflow-hidden bg-surface-container-high">' +
                '<img src="' +
                p.image_url +
                '" alt="' +
                p.keterangan +
                '" ' +
                "onclick=\"openImageModal('" +
                p.image_url +
                "')\" " +
                'class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-zoom-in" ' +
                "onerror=\"this.src=''\"></div>" +
                '<div class="p-3 space-y-2">' +
                (p.keterangan
                    ? '<p class="text-xs text-on-surface-variant line-clamp-2">' +
                    p.keterangan +
                    "</p>"
                    : "") +
                '<p class="text-[10px] text-white/20">' +
                p.tanggal +
                "</p>" +
                actions +
                "</div></div>"
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
    document.getElementById("btn_tambah").style.display =
        tab === "aktif" ? "" : "none";
    loadPhotos();
}

function openAddModal() {
    document.getElementById("form_add").reset();
    document.getElementById("modal_add").classList.remove("hidden");
}

function openEditModal(photo) {
    document.getElementById("edit_id").value = photo.id;
    document.getElementById("edit_keterangan").value = photo.keterangan;
    document.getElementById("edit_image").value = "";
    document.getElementById("modal_edit").classList.remove("hidden");
}

function closeModal(id) {
    document.getElementById(id).classList.add("hidden");
}

async function submitAdd(e) {
    e.preventDefault();
    var btn = document.getElementById("btn_add_submit");
    btn.disabled = true;
    btn.textContent = "Mengupload...";

    var form = new FormData();
    form.append("image", document.getElementById("add_image").files[0]);
    form.append("keterangan", document.getElementById("add_keterangan").value);

    try {
        var res = await fetch(window.portofolioStoreUrl, {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
            },
            body: form,
        });
        var data = await res.json();
        if (!res.ok) throw new Error(data.message || "Gagal mengunggah.");
        closeModal("modal_add");
        showToast(data.message, "green");
        loadPhotos();
    } catch (err) {
        showToast(err.message, "red");
    } finally {
        btn.disabled = false;
        btn.textContent = "Simpan";
    }
}

async function submitEdit(e) {
    e.preventDefault();
    var btn = document.getElementById("btn_edit_submit");
    btn.disabled = true;
    btn.textContent = "Menyimpan...";

    var id = document.getElementById("edit_id").value;
    var form = new FormData();
    form.append("keterangan", document.getElementById("edit_keterangan").value);
    form.append("_method", "PUT");

    var imageFile = document.getElementById("edit_image").files[0];
    if (imageFile) form.append("image", imageFile);

    try {
        var res = await fetch(
            window.portofolioUpdateUrl.replace("__ID__", id),
            {
                method: "POST",
                headers: {
                    Authorization: "Bearer " + TOKEN,
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: form,
            },
        );
        var data = await res.json();
        if (!res.ok) throw new Error(data.message || "Gagal menyimpan.");
        closeModal("modal_edit");
        showToast(data.message, "green");
        loadPhotos();
    } catch (err) {
        showToast(err.message, "red");
    } finally {
        btn.disabled = false;
        btn.textContent = "Simpan";
    }
}

var pendingConfirmAction = null;

function confirmDeletePhoto(id) {
    var iconWrap = document.getElementById("confirm_icon_wrap");
    var icon = document.getElementById("confirm_icon");
    var actionBtn = document.getElementById("confirm_action_btn");

    iconWrap.className =
        "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-red-500/10 border-red-500/30 text-red-500";
    icon.textContent = "delete";
    document.getElementById("confirm_title").textContent = "Hapus Foto?";
    document.getElementById("confirm_message").innerHTML =
        "Foto ini akan dipindahkan ke tab Terhapus. Anda bisa memulihkannya kapan saja.";
    actionBtn.className =
        "flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg bg-red-500 text-white hover:bg-red-600";
    actionBtn.textContent = "Ya, Hapus";

    pendingConfirmAction = function () {
        deletePhoto(id);
    };

    document.getElementById("confirm_modal").classList.remove("hidden");
}

function confirmRestorePhoto(id) {
    var iconWrap = document.getElementById("confirm_icon_wrap");
    var icon = document.getElementById("confirm_icon");
    var actionBtn = document.getElementById("confirm_action_btn");

    iconWrap.className =
        "w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border bg-green-500/10 border-green-500/30 text-green-500";
    icon.textContent = "restore";
    document.getElementById("confirm_title").textContent = "Pulihkan Foto?";
    document.getElementById("confirm_message").innerHTML =
        "Foto ini akan dikembalikan ke daftar Aktif.";
    actionBtn.className =
        "flex-1 py-3 rounded-2xl text-xs font-bold transition-colors shadow-lg bg-green-500 text-white hover:bg-green-600";
    actionBtn.textContent = "Ya, Pulihkan";

    pendingConfirmAction = function () {
        restorePhoto(id);
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

async function deletePhoto(id) {
    try {
        var res = await fetch(
            window.portofolioDestroyUrl.replace("__ID__", id),
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
        showToast(data.message, "green");
        loadPhotos();
    } catch (e) {
        showToast("Gagal menghapus.", "red");
    }
}

async function restorePhoto(id) {
    try {
        var res = await fetch(
            window.portofolioRestoreUrl.replace("__ID__", id),
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
        loadPhotos();
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

function openImageModal(url) {
    var modal = document.getElementById("image_modal");
    var img = document.getElementById("modal_image_content");
    img.src = url;
    modal.classList.remove("hidden");
    setTimeout(function () {
        modal.classList.remove("opacity-0");
        img.classList.remove("scale-95");
        img.classList.add("scale-100");
    }, 10);
}

function closeImageModal() {
    var modal = document.getElementById("image_modal");
    var img = document.getElementById("modal_image_content");
    modal.classList.add("opacity-0");
    img.classList.remove("scale-100");
    img.classList.add("scale-95");
    setTimeout(function () {
        modal.classList.add("hidden");
        img.src = "";
    }, 300);
}

document.addEventListener("DOMContentLoaded", loadPhotos);