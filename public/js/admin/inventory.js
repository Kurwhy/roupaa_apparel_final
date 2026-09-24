let rowCount = 0;

// Fungsi untuk membuat penundaan (debounce) pada search bar
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function filterInventory(value) {
    const q = value.trim().toLowerCase();
    const cards = document.querySelectorAll(".inventory-card");
    let anyVisible = false;

    cards.forEach(function (card) {
        const match = !q || card.getAttribute("data-search").indexOf(q) !== -1;
        card.style.display = match ? "" : "none";
        if (match) anyVisible = true;
    });

    const emptyState = document.getElementById("inventory_empty_state");
    if (emptyState) {
        emptyState.classList.toggle("hidden", !(q && !anyVisible));
    }
}

function openRestockModal() {
    const modal = document.getElementById("restockModal");
    const tbody = document.getElementById("restock-table-body");
    const form = modal.querySelector("form");

    form.action = "/ops/inventory/print-restock";
    form.target = "_blank";

    modal.querySelector("h2").innerText = "Daftar Pengajuan Belanja";
    modal.querySelector("p").innerText =
        "Sistem otomatis memasukkan barang dengan stok rendah. Anda bisa menyesuaikan jumlahnya.";

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML =
        '<span class="material-symbols-outlined text-[18px]">print</span> Cetak Kertas Pengajuan';
    submitBtn.className =
        "px-6 py-3 bg-primary text-black rounded-xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-transform flex items-center gap-2";

    document.getElementById("pengeluaran_section").classList.add("hidden");
    resetPengeluaranFields();

    tbody.innerHTML = "";
    rowCount = 0;

    if (window.lowStockItems && window.lowStockItems.length > 0) {
        window.lowStockItems.forEach((item) => {
            createRow(item.id, item.type, item.name, item.stock, item.unit, 10);
        });
    } else {
        addManualRow();
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeRestockModal() {
    document.getElementById("restockModal").classList.add("hidden");
    document.getElementById("restockModal").classList.remove("flex");
}

function createRow(id = "", type = "", name = "", stock = "-", unit = "Pcs", qty = 10) {
    const tr = document.createElement("tr");
    tr.className = "hover:bg-white/[0.02]";

    let itemField = "";

    if (name !== "") {
        itemField = `
            <div class="bg-surface border border-white/5 text-white/70 text-xs rounded-lg px-3 py-2 flex items-center gap-2 cursor-not-allowed">
                <span class="material-symbols-outlined text-[14px]">lock</span>
                <span class="truncate" title="${name}">${name}</span>
            </div>
            <input type="hidden" name="items[${rowCount}][id]" value="${id}">
            <input type="hidden" name="items[${rowCount}][type]" value="${type}">
            <input type="hidden" name="items[${rowCount}][name]" value="${name}">
        `;
    } else {
        let optionsHtml = `<option value="" disabled selected>-- Pilih Barang dari Gudang --</option>`;
        window.allItems.forEach((item) => {
            optionsHtml += `<option value="${item.name}" data-id="${item.id}" data-type="${item.type}" data-stock="${item.stock}" data-unit="${item.unit}">${item.name}</option>`;
        });
        itemField = `
            <select name="items[${rowCount}][name]" class="w-full bg-surface border border-white/10 text-white text-xs rounded-lg px-3 py-2 focus:border-primary cursor-pointer" onchange="lockAndFillRow(this, ${rowCount})">
                ${optionsHtml}
            </select>
        `;
    }

    tr.innerHTML = `
        <td class="py-3 pr-4" id="item-container-${rowCount}">${itemField}</td>
        <td class="py-3 px-2 text-center text-red-400 font-bold" id="stock-display-${rowCount}">
            <input type="hidden" name="items[${rowCount}][current_stock]" value="${stock}">
            ${stock} ${unit !== "Pcs" && stock !== "-" ? unit : ""}
        </td>
        <td class="py-3 px-2">
            <div class="flex items-center gap-2 justify-center">
                <input type="number" name="items[${rowCount}][qty]" value="${qty}" min="0" step="any" class="w-20 bg-surface border border-white/10 text-white text-center text-xs rounded-lg px-2 py-2 focus:border-primary">
                <input type="text" name="items[${rowCount}][unit]" id="unit-input-${rowCount}" value="${unit}" readonly class="w-16 bg-transparent text-white/50 text-center text-xs px-1 outline-none cursor-not-allowed">
            </div>
        </td>
        <td class="py-3 pl-4 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[16px]">delete</span>
            </button>
        </td>
    `;

    document.getElementById("restock-table-body").appendChild(tr);
    rowCount++;
}

function lockAndFillRow(selectEl, idx) {
    const selected = selectEl.options[selectEl.selectedIndex];
    if (!selected.value) return;

    const name = selected.value;
    const id = selected.getAttribute("data-id");
    const type = selected.getAttribute("data-type");
    const stock = selected.getAttribute("data-stock");
    const unit = selected.getAttribute("data-unit");

    document.getElementById(`stock-display-${idx}`).innerHTML = `
        <input type="hidden" name="items[${idx}][current_stock]" value="${stock}">
        ${stock} ${unit}
    `;
    document.getElementById(`unit-input-${idx}`).value = unit;

    const container = document.getElementById(`item-container-${idx}`);
    container.innerHTML = `
        <div class="bg-surface border border-white/5 text-primary text-xs rounded-lg px-3 py-2 flex items-center gap-2 cursor-not-allowed transition-all">
            <span class="material-symbols-outlined text-[14px]">lock</span>
            <span class="truncate" title="${name}">${name}</span>
        </div>
        <input type="hidden" name="items[${idx}][id]" value="${id}">
        <input type="hidden" name="items[${idx}][type]" value="${type}">
        <input type="hidden" name="items[${idx}][name]" value="${name}">
    `;
}

function addManualRow() {
    createRow("", "", "", "-", "Pcs", 10);
}

function openReceiveModal() {
    const modal = document.getElementById("restockModal");
    const tbody = document.getElementById("restock-table-body");
    const form = modal.querySelector("form");

    form.action = "/ops/inventory/apply-restock";
    form.target = "_self";

    modal.querySelector("h2").innerText = "Verifikasi Barang Masuk";
    modal.querySelector("p").innerText =
        "Pilih Draf Belanja (PO) yang barangnya baru saja datang. Sesuaikan jumlah jika ada yang batal/kurang.";

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML =
        '<span class="material-symbols-outlined text-[18px]">inventory</span> Simpan ke Gudang';
    submitBtn.className =
        "px-6 py-3 bg-green-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-green-600 transition-colors flex items-center gap-2 shadow-[0_0_15px_rgba(34,197,94,0.3)]";

    document.getElementById("pengeluaran_section").classList.remove("hidden");
    resetPengeluaranFields();

    tbody.innerHTML = "";
    rowCount = 0;

    if (!window.pendingBatches || window.pendingBatches.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-white/40 italic text-xs">Tidak ada Draf Belanja yang menggantung.</td></tr>`;
    } else {
        let poOptions = `<option value="" disabled selected style="background-color:#1c1c1e;color:#ffffff;">-- Pilih Kertas Daftar Belanja (PO) --</option>`;
        window.pendingBatches.forEach((batch) => {
            const tgl = new Date(batch.created_at).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
            const batchJson = JSON.stringify(batch).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
            poOptions += `<option value='${batchJson}' style="background-color:#1c1c1e;color:#ffffff;">${batch.invoice_number} (Tgl: ${tgl})</option>`;
        });
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-4 px-2 border-b border-white/5">
                    <select onchange="loadPendingBatch(this)" style="color-scheme: dark; background-color:#1c1c1e; color:#ffffff;" class="w-full border border-primary/50 text-sm font-bold rounded-xl px-4 py-3 focus:border-primary shadow-inner">
                        ${poOptions}
                    </select>
                    <p class="text-[9px] text-primary mt-2 uppercase tracking-widest"><span class="material-symbols-outlined text-[10px] inline-block align-middle">info</span> Pilih Nota untuk memuat daftar barang</p>
                </td>
            </tr>
        `;
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function loadPendingBatch(selectEl) {
    const batch = JSON.parse(selectEl.value);
    const tbody = document.getElementById("restock-table-body");

    const trBatchId = document.createElement("tr");
    trBatchId.innerHTML = `<td colspan="4"><input type="hidden" name="batch_id" value="${batch.id}"></td>`;

    const selectRow = tbody.firstElementChild;
    tbody.innerHTML = "";
    tbody.appendChild(selectRow);
    tbody.appendChild(trBatchId);

    rowCount = 0;
    batch.items_data.forEach((item) => {
        if (item.name) createRow(item.id, item.type, item.name, item.current_stock, item.unit, item.qty);
    });
}

function resetPengeluaranFields() {
    const inputTotal = document.getElementById("input_total_pengeluaran");
    const inputStruk = document.getElementById("struk_upload");
    const labelStruk = document.getElementById("struk_filename");
    if (inputTotal) inputTotal.value = "";
    if (inputStruk) inputStruk.value = "";
    if (labelStruk) labelStruk.innerText = "Pilih file struk (JPG/PNG)";
}

function openEditModal(id, type, name, stock, unit, hargaJual = 0) {
    document.getElementById("edit_item_id").value = id;
    document.getElementById("edit_item_type").value = type;
    document.getElementById("edit_item_name").textContent = name;

    let stockInput = document.getElementById("edit_item_stock");
    stockInput.value = stock;
    stockInput.dispatchEvent(new Event('input'));

    document.getElementById("edit_item_unit").textContent = unit;

    const hargaSection = document.getElementById("harga_jual_section");
    const hargaInput = document.getElementById("edit_harga_jual");

    if (type === "apparel") {
        hargaSection.classList.remove("hidden");
        hargaInput.value = hargaJual > 0 ? hargaJual : "";
        hargaInput.dispatchEvent(new Event('input'));
    } else {
        hargaSection.classList.add("hidden");
        hargaInput.value = "";
        hargaInput.dispatchEvent(new Event('input'));
    }

    document.getElementById("editStockModal").classList.remove("hidden");
    document.getElementById("editStockModal").classList.add("flex");
}

function closeEditModal() {
    document.getElementById("editStockModal").classList.add("hidden");
    document.getElementById("editStockModal").classList.remove("flex");
}

function simpanEditStok() {
    const errEl = document.getElementById("error_edit_item_stock");
    if (errEl) { errEl.classList.add("hidden"); errEl.textContent = ""; }

    const stockVal = document.getElementById("edit_item_stock").value;
    if (stockVal === "" || parseFloat(stockVal) < 0) {
        if (errEl) {
            errEl.textContent = "Stok tidak valid.";
            errEl.classList.remove("hidden");
        }
        return;
    }

    const hargaInputHidden = document.getElementById("hidden_edit_harga_jual");

    const btn = document.getElementById("btn_simpan_edit_stok");
    const originalHtml = btn ? btn.innerHTML : "";
    if (btn) { btn.innerHTML = "Menyimpan..."; btn.disabled = true; }

    const jwtToken = sessionStorage.getItem("access_token");
    const formData = new FormData();
    formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
    formData.append("item_id", document.getElementById("edit_item_id").value);
    formData.append("type", document.getElementById("edit_item_type").value);
    formData.append("new_stock", stockVal);

    if (hargaInputHidden && hargaInputHidden.value) {
        formData.append("harga_jual", hargaInputHidden.value);
    }

    fetch("/ops/inventory/update-stock", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            Authorization: "Bearer " + jwtToken,
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const msg = data.errors
                    ? Object.values(data.errors).flat()[0]
                    : (data.message || "Gagal menyimpan perubahan.");
                throw new Error(msg);
            }
        })
        .then(() => { window.location.reload(); })
        .catch((err) => { alert(err.message); console.error(err); })
        .finally(() => {
            if (btn) { btn.innerHTML = originalHtml; btn.disabled = false; }
        });
}

function openAddModal() {
    document.getElementById("addApparelModal").classList.remove("hidden");
    document.getElementById("addApparelModal").classList.add("flex");
}

function closeAddApparelModal() {
    document.getElementById("addApparelModal").classList.add("hidden");
    document.getElementById("addApparelModal").classList.remove("flex");
}

function handleProductTypeChange() {
    const selectEl = document.getElementById("add_product_id");
    if (!selectEl || selectEl.selectedIndex === -1) return;

    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const kategori = selectedOption.getAttribute("data-kategori") || "";

    const wrapLengan = document.getElementById("wrap_var_lengan");
    const wrapWarna = document.getElementById("wrap_var_warna");
    const wrapUkuranBaju = document.getElementById("wrap_var_ukuran_baju");
    const wrapKeterangan = document.getElementById("wrap_var_keterangan");
    const labelKeterangan = document.getElementById("label_var_keterangan");

    document.getElementById("add_var_lengan").value = "";
    document.getElementById("add_var_warna").value = "";
    document.getElementById("add_var_ukuran_baju").value = "";
    document.getElementById("add_var_keterangan").value = "";

    if (kategori.includes("bendera") || kategori.includes("stiker")) {
        wrapLengan.classList.add("hidden");
        wrapWarna.classList.add("hidden");
        wrapUkuranBaju.classList.add("hidden");

        wrapKeterangan.classList.remove("hidden");
        labelKeterangan.textContent = kategori.includes("stiker") ? "Ukuran Stiker (PxL) *" : "Ukuran Bendera (PxL) *";
        document.getElementById("add_var_keterangan").placeholder = "Cth: 10x10 cm";

    } else if (kategori.includes("topi")) {
        wrapLengan.classList.add("hidden");
        wrapUkuranBaju.classList.add("hidden");

        wrapWarna.classList.remove("hidden");
        wrapKeterangan.classList.remove("hidden");
        labelKeterangan.textContent = "Ukuran *";
        document.getElementById("add_var_keterangan").value = "All Size";
        document.getElementById("add_var_keterangan").placeholder = "Cth: All Size";

    } else if (kategori !== "") {
        wrapLengan.classList.remove("hidden");
        wrapWarna.classList.remove("hidden");
        wrapUkuranBaju.classList.remove("hidden");

        wrapKeterangan.classList.add("hidden");
    } else {
        wrapLengan.classList.add("hidden");
        wrapWarna.classList.add("hidden");
        wrapUkuranBaju.classList.add("hidden");
        wrapKeterangan.classList.add("hidden");
    }

    updateVariantPreview();
}

function updateVariantPreview() {
    let parts = [];

    if (!document.getElementById("wrap_var_lengan").classList.contains("hidden")) {
        parts.push(document.getElementById("add_var_lengan").value);
    }
    if (!document.getElementById("wrap_var_warna").classList.contains("hidden")) {
        parts.push(document.getElementById("add_var_warna").value.trim());
    }
    if (!document.getElementById("wrap_var_ukuran_baju").classList.contains("hidden")) {
        parts.push(document.getElementById("add_var_ukuran_baju").value);
    }
    if (!document.getElementById("wrap_var_keterangan").classList.contains("hidden")) {
        parts.push(document.getElementById("add_var_keterangan").value.trim());
    }

    const combined = parts.filter(Boolean).join(" - ");

    const preview = document.getElementById("add_var_preview");
    if (preview) preview.textContent = combined || "-";

    const hidden = document.getElementById("add_variant_name_hidden");
    if (hidden) hidden.value = combined || "Standar";
}

function openDeleteModal(id, name) {
    document.getElementById("delete_item_name").textContent = name;
    document.getElementById("delete_error_message").classList.add("hidden");
    document.getElementById("form_delete_apparel").action = `/ops/inventory/apparel/${id}`;
    document.getElementById("deleteStockModal").classList.remove("hidden");
    document.getElementById("deleteStockModal").classList.add("flex");
}

function closeDeleteModal() {
    document.getElementById("deleteStockModal").classList.add("hidden");
    document.getElementById("deleteStockModal").classList.remove("flex");
}

document.addEventListener("DOMContentLoaded", function () {
    var searchEl = document.getElementById("search_input");
    if (searchEl) {
        searchEl.addEventListener("input", debounce(function () {
            filterInventory(this.value);
        }, 400));
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const jwtToken = sessionStorage.getItem("access_token");

    const productSelect = document.getElementById("add_product_id");
    if (productSelect) productSelect.addEventListener("change", handleProductTypeChange);

    ["add_var_lengan", "add_var_warna", "add_var_ukuran_baju", "add_var_keterangan"].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", updateVariantPreview);
    });

    document.addEventListener("submit", function (e) {
        const form = e.target;

        if (form.id === "form_edit_stock") return;

        if (form.action.includes("print-restock")) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> Memproses...';
            submitBtn.disabled = true;

            const printWindow = window.open("", "_blank");
            if (!printWindow) {
                alert("Popup diblokir browser. Izinkan popup untuk halaman ini lalu coba lagi.");
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }
            printWindow.document.write('<p style="font-family:sans-serif;padding:20px">Memuat dokumen...</p>');

            fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "text/html",
                    Authorization: "Bearer " + jwtToken,
                },
            })
                .then((res) => {
                    if (!res.ok) throw new Error("Gagal memuat data cetak. Coba lagi.");
                    return res.text();
                })
                .then((html) => {
                    printWindow.document.open();
                    printWindow.document.write(html);
                    printWindow.document.close();
                    closeRestockModal();
                })
                .catch((err) => {
                    console.error(err);
                    printWindow.close();
                    alert("Terjadi kesalahan saat memproses cetak. Silakan coba lagi.");
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });

            return;
        }

        if (form.action.includes("apply-restock")) {
            const totalPengeluaran = document.getElementById("input_total_pengeluaran").value;
            const strukFile = document.getElementById("struk_upload").files[0];

            if (!totalPengeluaran) {
                e.preventDefault();
                alert("Total pengeluaran wajib diisi.");
                return;
            }
            if (!strukFile) {
                e.preventDefault();
                alert("Struk/bukti pembelian wajib diupload.");
                return;
            }
        }

        if (form.action.includes("/inventory/")) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            let originalText = "";
            if (submitBtn) {
                originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = "Memproses...";
                submitBtn.disabled = true;
            }

            fetch(form.action, {
                method: form.method,
                body: new FormData(form),
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    Authorization: "Bearer " + jwtToken,
                },
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const errMsg = data.errors
                            ? Object.values(data.errors).flat()[0]
                            : (data.message || "Gagal memproses data. Silakan coba lagi.");
                        throw new Error(errMsg);
                    }
                    return data;
                })
                .then(() => { window.location.reload(); })
                .catch((err) => {
                    const deleteErrorEl = document.getElementById("delete_error_message");
                    if (form.id === "form_delete_apparel" && deleteErrorEl) {
                        deleteErrorEl.textContent = err.message;
                        deleteErrorEl.classList.remove("hidden");
                    } else {
                        alert(err.message);
                    }
                    console.error(err);
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                });
        }
    });
});