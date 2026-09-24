function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

document.addEventListener("DOMContentLoaded", () => {
    const CONFIG = window.APP_CONFIG;
    let orderData = null;

    const orderId = window.ORDER_ID;
    const _token = sessionStorage.getItem("access_token");
    if (orderId && _token) {
        fetch(`/customer/api/notifications/order/${orderId}/read`, {
            method: "POST",
            headers: {
                Authorization: "Bearer " + _token,
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
                Accept: "application/json",
            },
        });
    }

    function formatDate(dateString) {
        const d = new Date(dateString);
        return d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            timeZone: "Asia/Jakarta",
        });
    }

    function showSpecError(message) {
        const container = document.getElementById("spec_error_container");
        const msgEl = document.getElementById("spec_error_message");
        if (!container || !msgEl) return;
        msgEl.textContent = message;
        container.classList.remove("hidden");
        container.classList.add("flex");
    }

    function hideSpecError() {
        const container = document.getElementById("spec_error_container");
        if (container) {
            container.classList.add("hidden");
            container.classList.remove("flex");
        }
    }

    function scrollToBottom() {
        const chatArea = document.getElementById("chat_area");
        if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;
    }

    window.openImageModal = function (src) {
        document.getElementById("modal_image_content").src = src;
        const downloadBtn = document.getElementById("btn_download_modal");
        if (downloadBtn) downloadBtn.href = src;

        const modal = document.getElementById("image_modal");
        if (modal) {
            modal.classList.remove("hidden");
            setTimeout(() => {
                modal.classList.remove("opacity-0");
                modal.children[1].children[0].classList.remove("scale-95");
            }, 10);
        }
    };

    window.sendReadReceipt = function () {
        if (!CONFIG.markReadUrl || !CONFIG.apiToken) return;

        fetch(CONFIG.markReadUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": CONFIG.csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
                Authorization: "Bearer " + CONFIG.apiToken,
            },
        }).catch(() => { });
    };

    window.closeImageModal = function () {
        const modal = document.getElementById("image_modal");
        if (modal) {
            modal.classList.add("opacity-0");
            modal.children[1].children[0].classList.add("scale-95");
            setTimeout(() => {
                modal.classList.add("hidden");
            }, 300);
        }
    };

    let fetchRetryCount = 0;

    window.fetchOrderData = async function () {
        let json;
        try {
            const res = await fetch(CONFIG.apiUrl, {
                headers: {
                    Authorization: "Bearer " + CONFIG.apiToken,
                    Accept: "application/json",
                },
            });

            if (!res.ok) throw new Error("HTTP " + res.status);
            json = await res.json();
            fetchRetryCount = 0;
        } catch (error) {
            console.error("[System] API fetch error:", error);

            if (sessionStorage.getItem("pending_payment_confirm")) {
                console.info("[System] Payment redirect aktif, skip error.");
                return;
            }

            fetchRetryCount++;
            if (fetchRetryCount <= 2) {
                console.info("[System] Retry fetch ke-" + fetchRetryCount);
                setTimeout(() => window.fetchOrderData(), 1500);
                return;
            }

            alert("Sesi berakhir atau order tidak ditemukan.");
            window.location.href = "/customer/progress-pesanan";
            return;
        }

        orderData = json.data;
        try {
            renderUI();
            renderChat();
        } catch (renderError) {
            console.error("[System] Render error:", renderError);
        }
    };

    function renderUI() {
        const pageLoader = document.getElementById("page_loader");
        if (pageLoader) pageLoader.classList.add("hidden");

        document.title = `Ruang Project | ${orderData.project_name}`;
        document.getElementById("ui_project_name").innerText =
            orderData.project_name;
        document.getElementById("ui_order_number").innerText =
            `#${orderData.order_number}`;
        document.getElementById("ui_order_date").innerHTML =
            `<span class="material-symbols-outlined text-[13px]">calendar_today</span> ${formatDate(orderData.created_at)}`;

        const statusConfig = {
            diskusi_desain: {
                icon: "design_services",
                label: "Fase Desain",
                isSpin: false,
            },
            menunggu_spesifikasi: {
                icon: "assignment",
                label: "Isi Spesifikasi",
                isSpin: false,
            },
            menunggu_estimasi: {
                icon: "calculate",
                label: "Menunggu Estimasi",
                isSpin: false,
            },
            menunggu_pembayaran: {
                icon: "payments",
                label: "Menunggu Pembayaran",
                isSpin: false,
            },
            diproses: {
                icon: "precision_manufacturing",
                label: "Proses Produksi",
                isSpin: false,
            },
            siap_diambil: {
                icon: "inventory_2",
                label: "Siap Diambil/Kirim",
                isSpin: false,
            },
            selesai: { icon: "check_circle", label: "Selesai", isSpin: false },
            dibatalkan: { icon: "cancel", label: "Dibatalkan", isSpin: false },
        };
        const currentStatus = statusConfig[orderData.status] || {
            icon: "info",
            label: orderData.status.replace(/_/g, " ").toUpperCase(),
            isSpin: false,
        };

        const iconEl = document.getElementById("ui_status_icon");
        if (iconEl) {
            iconEl.innerText = currentStatus.icon;
            if (currentStatus.isSpin) iconEl.classList.add("animate-spin");
        }
        document.getElementById("ui_status_label").innerText =
            currentStatus.label;

        const panelCanvas = document.getElementById("panel_design_canvas");
        const panelUnlocked = document.getElementById("panel_unlocked");

        if (orderData.status === "diskusi_desain") {
            if (panelCanvas)
                panelCanvas.classList.remove(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );
            if (panelUnlocked)
                panelUnlocked.classList.add(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );
        } else if (
            orderData.status === "menunggu_spesifikasi" ||
            orderData.status === "menunggu_estimasi"
        ) {
            if (panelUnlocked)
                panelUnlocked.classList.remove(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );
            if (panelCanvas)
                panelCanvas.classList.add(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );

            const specProdName = document.getElementById(
                "ui_spec_product_name",
            );
            if (specProdName) specProdName.innerText = orderData.product.name;
            window.appProductAttributes = orderData.product.attributes;
            window.appInventories = orderData.product?.inventories || [];
            if (orderData.items && orderData.items.length > 0) {
                renderSavedItems();
            } else {
                const rowsContainer = document.getElementById("rows_container");
                if (rowsContainer && rowsContainer.children.length === 0) {
                    window.addNewRow();
                }
            }
        } else if (
            orderData.status === "diproses" ||
            orderData.status === "siap_diambil" ||
            orderData.status === "selesai"
        ) {
            if (panelCanvas)
                panelCanvas.classList.add(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );
            if (panelUnlocked)
                panelUnlocked.classList.add(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );

            renderProductionPanel();
        } else {
            if (panelCanvas)
                panelCanvas.classList.add(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );
            if (panelUnlocked)
                panelUnlocked.classList.add(
                    "opacity-0",
                    "pointer-events-none",
                    "scale-95",
                );
        }

        const rawMockup = orderData.final_mockup_path;
        const mockups = Array.isArray(rawMockup)
            ? rawMockup
            : typeof rawMockup === "string" && rawMockup
                ? JSON.parse(rawMockup)
                : [];
        const hasMockup = mockups.length > 0;

        const biayaSablon = parseFloat(orderData.biaya_sablon) || 0;
        const biayaSablonContainer = document.getElementById(
            "ui_biaya_sablon_container",
        );
        if (biayaSablonContainer) {
            if (biayaSablon > 0) {
                biayaSablonContainer.classList.remove("hidden");
                document.getElementById("ui_biaya_sablon_value").textContent =
                    "Rp " + biayaSablon.toLocaleString("id-ID");
            } else {
                biayaSablonContainer.classList.add("hidden");
            }
        }
        window.appInventories = orderData.product?.inventories || [];

        if (hasMockup) {
            const mockupHtml = mockups
                .map(
                    (path) => `
                <div class="w-full h-40 bg-surface-container-high border border-white/20 rounded-xl overflow-hidden relative group shadow-md cursor-zoom-in" onclick="window.openImageModal('/storage/${path}')">
                    <img src="/storage/${path}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>`,
                )
                .join("");
            document.getElementById("ui_mockup_container").innerHTML =
                mockupHtml;
            document
                .getElementById("ui_mockup_container")
                .classList.remove("hidden");

            const specMockupList = document.getElementById(
                "ui_spec_mockup_list",
            );
            if (specMockupList) {
                specMockupList.innerHTML = mockups
                    .map(
                        (path) => `
                        <div class="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden border border-white/20 relative cursor-zoom-in" onclick="window.openImageModal('/storage/${path}')">
                        <img src="/storage/${path}" class="w-full h-full object-cover">
                        </div>`,
                    )
                    .join("");
            }
            const specMockupContainer = document.getElementById(
                "ui_spec_mockup_container",
            );
            if (specMockupContainer)
                specMockupContainer.classList.remove("hidden");
            document.getElementById("ui_no_mockup")?.classList.add("hidden");
        } else {
            document.getElementById("ui_no_mockup")?.classList.remove("hidden");
            document
                .getElementById("ui_mockup_container")
                ?.classList.add("hidden");
        }

        if (orderData.reference_file_path) {
            document
                .getElementById("ui_reference_image")
                .classList.remove("hidden");
            document
                .getElementById("ui_reference_image")
                .querySelector("img").src =
                `/storage/${orderData.reference_file_path}`;
            document
                .getElementById("ui_reference_image")
                .setAttribute(
                    "onclick",
                    `window.openImageModal('/storage/${orderData.reference_file_path}')`,
                );
        }
        if (orderData.design_notes) {
            document
                .getElementById("ui_design_notes")
                .classList.remove("hidden");
            document
                .getElementById("ui_design_notes")
                .querySelector("p.text-xs").innerText = orderData.design_notes;
        }
        if (!orderData.reference_file_path && !orderData.design_notes) {
            document
                .getElementById("ui_no_reference")
                .classList.remove("hidden");
        }

        const actionContainer = document.getElementById(
            "action_buttons_container",
        );
        if (orderData.status === "diskusi_desain" && actionContainer) {
            if (hasMockup) {
                if (!orderData.is_design_approved) {
                    actionContainer.innerHTML = `
                        <p class="text-[10px] text-on-surface-variant/70 text-center mb-4 leading-relaxed px-4">Jika preview di atas sudah sesuai, tekan tombol di bawah ini.</p>
                        <button onclick="window.approveDesign()" class="w-full bg-green-500/10 text-green-400 border border-green-500/30 py-4 font-headline font-bold uppercase text-sm tracking-[0.2em] rounded-xl hover:bg-green-500 hover:text-white transition-all">
                            <span class="material-symbols-outlined align-middle mr-2">verified</span> Setujui Desain
                        </button>
                    `;
                } else {
                    actionContainer.innerHTML = `
                        <p class="text-[10px] text-primary text-center mb-4 font-bold tracking-widest uppercase">Desain Telah Anda Setujui</p>
                        <button disabled class="w-full bg-surface-container-highest text-white border border-white/10 py-4 font-headline font-bold uppercase text-sm tracking-[0.2em] rounded-xl animate-pulse">
                            Menunggu Admin Memproses...
                        </button>
                    `;
                }
            } else {
                actionContainer.innerHTML = `
                    <p class="text-[10px] text-on-surface-variant/50 text-center mb-4 leading-relaxed px-4 italic">Tombol persetujuan akan terbuka setelah Tim Desain mengunggah Mockup Final.</p>
                    <button disabled class="w-full bg-surface-container-highest text-on-surface-variant border border-white/10 py-4 font-headline font-bold uppercase text-sm tracking-[0.2em] rounded-xl opacity-50 cursor-not-allowed">
                        Menunggu Desain...
                    </button>
                `;
            }
        }
    }

    function renderChat() {
        const chatContainer = document.getElementById(
            "chat_messages_container",
        );
        if (!orderData.chats || orderData.chats.length === 0) {
            chatContainer.innerHTML = `
                <div class="m-auto text-center flex flex-col items-center justify-center h-full opacity-50 pt-20">
                    <div class="w-16 h-16 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl text-white">chat_bubble</span>
                    </div>
                    <h4 class="text-white font-bold mb-1">Belum Ada Percakapan</h4>
                    <p class="text-xs text-on-surface-variant max-w-xs">Kirim pesan pertama untuk memulai diskusi desain.</p>
                </div>`;
            return;
        }

        chatContainer.innerHTML = "";
        orderData.chats.forEach((chat) =>
            window.appendSingleMessage(chat, false),
        );
    }

    function renderProductionPanel() {
        const panelUnlocked = document.getElementById("panel_unlocked");
        if (!panelUnlocked) return;

        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;
        const finalPrice = parseFloat(orderData?.final_price) || 0;
        const dpAmount = parseFloat(orderData?.dp_amount) || 0;
        const paymentStatus = orderData?.payment_status || "unpaid";
        const paymentType = orderData?.payment_type || "-";
        const sisaLunas = finalPrice - dpAmount;
        const orderStatus = orderData?.status || "diproses";

        const payBadge =
            {
                unpaid: `<span class="bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-1 rounded text-[9px] font-bold uppercase">Belum Bayar</span>`,
                dp_paid: `<span class="bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 px-2 py-1 rounded text-[9px] font-bold uppercase">DP Lunas</span>`,
                paid: `<span class="bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-1 rounded text-[9px] font-bold uppercase">Lunas</span>`,
            }[paymentStatus] || "";

        let productionBanner = "";
        if (orderStatus === "diproses") {
            productionBanner = `
            <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-4 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0 border border-blue-500/30">
                    <span class="material-symbols-outlined text-blue-400 text-[20px]">precision_manufacturing</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-blue-400 uppercase tracking-widest">Sedang Diproduksi</p>
                    <p class="text-[10px] text-on-surface-variant mt-1 leading-relaxed">
                        Pesanan Anda sedang dalam proses produksi oleh tim kami.
                        ${paymentStatus === "dp_paid"
                    ? "Pengiriman akan dilakukan setelah pelunasan sisa pembayaran."
                    : "Kami akan memberitahu Anda ketika pesanan siap."
                }
                    </p>
                </div>
            </div>
        `;
        } else if (orderStatus === "siap_diambil") {
            productionBanner = `
            <div class="bg-green-500/5 border border-green-500/20 rounded-xl p-4 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center flex-shrink-0 border border-green-500/30">
                    <span class="material-symbols-outlined text-green-400 text-[20px]">check_circle</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-green-400 uppercase tracking-widest">Produksi Selesai!</p>
                    <p class="text-[10px] text-on-surface-variant mt-1 leading-relaxed">
                        ${paymentStatus === "dp_paid"
                    ? "Pesanan sudah selesai diproduksi. Silakan lunasi sisa pembayaran untuk pengambilan/pengiriman."
                    : "Pesanan sudah selesai dan siap untuk diambil atau dikirimkan."
                }
                    </p>
                </div>
            </div>
        `;
        } else if (orderStatus === "selesai") {
            productionBanner = `
            <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center flex-shrink-0 border border-primary/30">
                    <span class="material-symbols-outlined text-primary text-[20px]">verified</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-primary uppercase tracking-widest">Pesanan Selesai</p>
                    <p class="text-[10px] text-on-surface-variant mt-1 leading-relaxed">
                        Terima kasih telah mempercayakan pesanan Anda kepada ROUPAA Apparel!
                    </p>
                </div>
            </div>
        `;
        }

        let productionPhotosHtml = "";
        if (orderData.production_photo_path) {
            const rawPhotos = orderData.production_photo_path;
            const photos = Array.isArray(rawPhotos)
                ? rawPhotos
                : typeof rawPhotos === "string" && rawPhotos
                    ? JSON.parse(rawPhotos)
                    : [];

            if (photos.length > 0) {
                const photosGrid = photos
                    .map(
                        (path) => `
                    <div class="w-full h-32 rounded-xl overflow-hidden border border-white/10 cursor-zoom-in shadow-md" onclick="window.openImageModal('/storage/${path}')">
                        <img src="/storage/${path}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>`,
                    )
                    .join("");

                productionPhotosHtml = `
                <div class="bg-surface-container-low border border-white/10 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px]">photo_camera</span>
                        Foto Hasil Produksi
                    </p>
                    <div class="grid grid-cols-2 gap-2">${photosGrid}</div>
                </div>
            `;
            }
        }

        let itemsHtml = "";
        if (orderData.items && orderData.items.length > 0) {
            orderData.items.forEach((item) => {
                const inv = item.product_inventory || item.productInventory;
                const variantName = inv?.variant_name || "-";
                const unitPrice = parseFloat(item.unit_price) || 0;
                const subtotal = parseFloat(item.subtotal) || 0;
                itemsHtml += `
                <div class="flex items-start justify-between py-2.5 border-b border-white/5 last:border-0">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-white">${variantName}</p>
                        <p class="text-[10px] text-on-surface-variant mt-0.5">
                            Rp ${unitPrice.toLocaleString("id-ID")}/pcs × ${item.qty} pcs
                        </p>
                    </div>
                    <p class="text-sm font-black text-primary font-mono ml-4">
                        Rp ${subtotal.toLocaleString("id-ID")}
                    </p>
                </div>
            `;
            });
        }

        const lunasBtn =
            paymentStatus === "dp_paid"
                ? `
        <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-4">
            <p class="text-[10px] text-yellow-400 font-bold uppercase tracking-widest text-center mb-1">
                Sisa Pembayaran
            </p>
            <p class="text-lg font-headline font-black text-yellow-400 text-center mb-3">
                Rp ${sisaLunas.toLocaleString("id-ID")}
            </p>
            <button onclick="window.showLunasModal()"
                class="w-full bg-yellow-500 text-black py-3 rounded-xl font-headline font-black text-xs uppercase tracking-widest hover:brightness-110 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[16px]">payments</span>
                Bayar Pelunasan
            </button>
        </div>
    `
                : "";

        let deliveryInfoHtml = "";
        if (orderStatus === "siap_diambil" || orderStatus === "selesai") {
            const alamat =
                orderData.pelanggan?.alamat ||
                orderData.alamat_pengiriman ||
                null;
            deliveryInfoHtml = `
            <div class="bg-surface-container-low border border-white/10 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                    Pengambilan / Pengiriman
                </p>
                <div class="space-y-2">
                    <div class="flex items-center gap-3 p-3 bg-surface rounded-lg border border-white/5">
                        <span class="material-symbols-outlined text-primary text-[18px]">storefront</span>
                        <div>
                            <p class="text-[10px] font-bold text-white uppercase">Ambil di Tempat</p>
                            <p class="text-[9px] text-on-surface-variant">Hubungi kami via chat untuk jadwal pengambilan</p>
                        </div>
                    </div>
                    ${alamat
                    ? `
                    <div class="flex items-start gap-3 p-3 bg-surface rounded-lg border border-white/5">
                        <span class="material-symbols-outlined text-primary text-[18px] mt-0.5">location_on</span>
                        <div>
                            <p class="text-[10px] font-bold text-white uppercase">Kirim ke Alamat</p>
                            <p class="text-[9px] text-on-surface-variant">${alamat}</p>
                            <p class="text-[8px] text-on-surface-variant/50 mt-1">Ongkos kirim diinformasikan via chat</p>
                        </div>
                    </div>
                    `
                    : ""
                }
                </div>
            </div>
        `;
        }

        panelUnlocked.innerHTML = `
        <div class="h-1 w-full bg-primary flex-shrink-0"></div>

        <div class="p-5 border-b border-primary/20 bg-primary/5 flex-shrink-0">
            <h3 class="font-headline font-black uppercase tracking-widest text-primary text-base flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center border border-primary/40">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                </div>
                Rincian Pesanan
            </h3>
            <p class="text-[10px] text-on-surface-variant uppercase mt-2 tracking-widest">
                Produk: <strong class="text-white">${orderData.product?.name || "-"}</strong>
            </p>
        </div>

        <div class="flex-grow p-5 overflow-y-auto custom-scrollbar space-y-4">

            ${productionBanner}

            ${productionPhotosHtml}

            <div class="bg-surface-container-low border border-white/10 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">payments</span>
                    Status Pembayaran
                </p>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] text-on-surface-variant">Status</span>
                    ${payBadge}
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] text-on-surface-variant">Metode</span>
                    <span class="text-xs font-bold text-white uppercase">
                        ${paymentType === "dp" ? "Down Payment 50%" : paymentType === "lunas" ? "Pembayaran Lunas" : paymentType === "pelunasan" ? "Pelunasan" : "-"}
                    </span>
                </div>
                ${paymentType === "dp" || paymentStatus === "dp_paid"
                ? `
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] text-on-surface-variant">DP Dibayar</span>
                    <span class="text-xs font-bold text-primary">Rp ${dpAmount.toLocaleString("id-ID")}</span>
                </div>
                ${paymentStatus !== "paid"
                    ? `
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-on-surface-variant">Sisa</span>
                    <span class="text-xs font-bold text-yellow-400">Rp ${sisaLunas.toLocaleString("id-ID")}</span>
                </div>
                `
                    : ""
                }
                `
                : ""
            }
            </div>

            <div class="bg-surface-container-low border border-white/10 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px]">list_alt</span>
                    Detail Kombinasi
                </p>
                ${itemsHtml || '<p class="text-[10px] text-on-surface-variant/50 italic text-center py-2">Tidak ada data item.</p>'}
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/10">
                    <span class="text-[10px] font-bold text-white uppercase">Total ${orderData.total_quantity || 0} pcs</span>
                    <span class="text-base font-headline font-black text-primary">
                        Rp ${finalPrice.toLocaleString("id-ID")}
                    </span>
                </div>
            </div>

            ${lunasBtn}
            ${deliveryInfoHtml}

        </div>
    `;

        panelUnlocked.classList.remove(
            "opacity-0",
            "pointer-events-none",
            "scale-95",
        );
    }

    function renderSavedItems() {
        const rowsContainer = document.getElementById("rows_container");
        if (!rowsContainer) return;

        rowsContainer.innerHTML = "";
        rowCount = 0;

        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;

        orderData.items.forEach((item) => {
            rowCount++;
            const rc = rowCount;
            const inv = (window.appInventories || []).find(
                (i) => i.id === item.product_inventory_id,
            );
            if (!inv) return;

            const hargaKain = parseFloat(inv.harga_jual) || 0;

            const lenganAttr = (window.appProductAttributes || []).find((a) =>
                a.name.toLowerCase().includes("lengan"),
            );

            let lenganValue = "";
            let filtered = [];

            if (lenganAttr) {
                const variantLower = inv.variant_name.toLowerCase();
                const isPanjang = variantLower.includes("panjang");
                lenganValue = isPanjang ? "Lengan Panjang" : "Lengan Pendek";
                const keyword = lenganValue.split(" ").pop().toLowerCase();
                filtered = (window.appInventories || []).filter((i) =>
                    i.variant_name.toLowerCase().includes(keyword),
                );
            } else {
                filtered = window.appInventories || [];
            }

            let invOptions = `<option value="" data-harga="0">-- Pilih Varian --</option>`;
            filtered.forEach((i) => {
                const h = parseFloat(i.harga_jual) || 0;
                const total = h + biayaSablon;
                const parts = i.variant_name.split(" - ");
                const label =
                    parts.length > 1
                        ? parts.slice(1).join(" - ")
                        : i.variant_name;
                const sel =
                    i.id === item.product_inventory_id ? "selected" : "";
                invOptions += `<option value="${i.id}" data-harga="${h}" ${sel}>
                    ${label} — Rp ${total.toLocaleString("id-ID")}/pcs
                </option>`;
            });

            let lenganRadioHtml = "";
            if (lenganAttr) {
                const radios = lenganAttr.values
                    .map(
                        (val) => `
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                            name="items[${rc}][lengan]"
                            value="${val.value}"
                            data-lengan="${val.value}"
                            ${val.value === lenganValue ? "checked" : ""}
                            onchange="window.filterVarianByLengan(${rc})"
                            class="lengan-radio accent-primary w-3.5 h-3.5 cursor-pointer">
                        <span class="text-xs text-on-surface-variant">${val.value}</span>
                    </label>
                `,
                    )
                    .join("");
                lenganRadioHtml = `
                    <div>
                        <label class="block text-[9px] uppercase tracking-widest text-on-surface-variant mb-2">
                            Jenis Lengan <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4 flex-wrap">${radios}</div>
                    </div>
                `;
            }

            const rowDiv = document.createElement("div");
            rowDiv.className =
                "p-4 bg-surface-container-low border border-outline-variant/30 rounded-xl relative group shadow-md";
            rowDiv.setAttribute("data-row", rc);

            if (rowCount > 1) {
                const deleteBtn = document.createElement("button");
                deleteBtn.type = "button";
                deleteBtn.className =
                    "absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10";
                deleteBtn.innerHTML =
                    '<span class="material-symbols-outlined text-[12px]">close</span>';
                deleteBtn.onclick = function () {
                    rowDiv.remove();
                    window.calculateTotal();
                };
                rowDiv.appendChild(deleteBtn);
            }

            const labelDiv = document.createElement("div");
            labelDiv.className =
                "text-[9px] uppercase tracking-widest text-primary mb-3 border-b border-outline-variant/10 pb-1";
            labelDiv.textContent = `Kombinasi #${rc}`;
            rowDiv.appendChild(labelDiv);

            const gridDiv = document.createElement("div");
            gridDiv.className = "flex flex-col gap-3";
            gridDiv.innerHTML = `
                ${lenganRadioHtml}
                <div>
                    <label class="block text-[9px] uppercase tracking-widest text-on-surface-variant mb-1">
                        Varian (Warna & Ukuran) <span class="text-red-500">*</span>
                    </label>
                    <select name="items[${rc}][product_inventory_id]" required
                        id="inv-select-${rc}"
                        onchange="window.calculateTotal()"
                        class="inv-select w-full bg-background border border-outline-variant/30 text-on-surface text-xs p-2 rounded-lg focus:border-primary appearance-none">
                        ${invOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] uppercase tracking-widest text-on-surface-variant mb-1">
                        Jumlah (Qty) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                        name="items[${rc}][qty]"
                        required min="1" value="${item.qty}"
                        oninput="window.calculateTotal()"
                        class="qty-input w-full bg-background border border-outline-variant/30 text-primary font-bold text-xs p-2 rounded-lg focus:border-primary">
                </div>
            `;

            rowDiv.appendChild(gridDiv);
            rowsContainer.appendChild(rowDiv);
        });

        window.calculateTotal();
    }

    window.showLunasModal = async function () {
        const finalPrice = parseFloat(orderData?.final_price) || 0;
        const dpAmount = parseFloat(orderData?.dp_amount) || 0;
        const sisaLunas = finalPrice - dpAmount;

        if (sisaLunas <= 0) {
            alert("Pesanan sudah lunas.");
            return;
        }

        try {
            const res = await fetch(
                `/customer/order/${CONFIG.orderId}/snap-token`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: "Bearer " + CONFIG.apiToken,
                        Accept: "application/json",
                        "X-CSRF-TOKEN": CONFIG.csrfToken,
                    },
                    body: JSON.stringify({
                        payment_type: "pelunasan",
                        final_price: sisaLunas,
                    }),
                },
            );

            const data = await res.json();
            if (!res.ok) {
                alert(data.error || "Gagal memproses permintaan.");
                return;
            }

            window.snap.pay(data.snap_token, {
                onSuccess: async function () {
                    console.log("[Midtrans] Pelunasan berhasil.");
                    sessionStorage.setItem(
                        "pending_payment_confirm",
                        CONFIG.orderId,
                    );

                    try {
                        await fetch(
                            `/customer/order/${CONFIG.orderId}/confirm-payment`,
                            {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    Authorization: "Bearer " + CONFIG.apiToken,
                                    Accept: "application/json",
                                    "X-CSRF-TOKEN": CONFIG.csrfToken,
                                },
                            },
                        );
                        sessionStorage.removeItem("pending_payment_confirm");
                    } catch (e) {
                        console.warn("[Payment] Redirect terjadi.");
                    }
                    window.fetchOrderData();
                },
                onPending: function () {
                    console.log("[Midtrans] Pelunasan pending.");
                    sessionStorage.setItem(
                        "pending_payment_confirm",
                        CONFIG.orderId,
                    );
                },
                onError: function (r) {
                    alert("Pembayaran gagal. Silakan coba lagi.");
                },
                onClose: function () {
                    if (sessionStorage.getItem("pending_payment_confirm")) {
                        window.location.reload();
                    }
                },
            });
        } catch (e) {
            alert("Koneksi gagal. Silakan periksa koneksi Anda.");
        }
    };

    window.appendSingleMessage = function (chat, isInstant = true) {
        const chatContainer = document.getElementById(
            "chat_messages_container",
        );
        if (!chatContainer) return;

        const emptyState = chatContainer.querySelector(".m-auto.text-center");
        if (emptyState) chatContainer.innerHTML = "";

        const isMe = String(chat.user_id) === String(CONFIG.currentUserId);

        let timeStr = chat.created_at
            ? formatDate(chat.created_at).split(",")[1]
            : new Date().toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
            });

        let tickHtml =
            isMe && !isInstant
                ? `<span class="material-symbols-outlined text-[13px] ml-1 read-tick ${chat.read_at ? "text-blue-500" : "text-inherit opacity-70"}">done_all</span>`
                : isMe && isInstant
                    ? `<span class="material-symbols-outlined text-[13px] ml-1 read-tick text-inherit opacity-70">done_all</span>`
                    : "";

        let html = "";
        if (chat.is_system_message) {
            html = `
                <div class="flex justify-center my-2 animate-[float_0.3s_ease-out]">
                    <span class="bg-surface border border-white/10 text-on-surface-variant text-[10px] px-4 py-2 rounded-full font-medium shadow-sm">
                        <span class="material-symbols-outlined text-[12px] align-middle mr-1">info</span> ${escapeHtml(chat.message)}
                    </span>
                </div>`;
        } else {
            html = `
                <div class="flex flex-col ${isMe ? "items-end" : "items-start"} w-full group/chat animate-[float_0.3s_ease-out]">
                    <div class="flex items-center gap-2 mb-1.5 px-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider ${isMe ? "text-primary" : "text-white"}">${isMe ? "Anda" : "Tim Desain"}</span>
                        <span class="text-[9px] text-on-surface-variant/50 flex items-center">
                            ${timeStr} ${tickHtml}
                        </span>
                    </div>
                    <div class="max-w-[75%] p-4 text-sm shadow-md relative ${isMe ? "bg-primary text-black rounded-2xl rounded-tr-sm" : "bg-surface border border-white/10 text-white rounded-2xl rounded-tl-sm"}">
                        ${chat.message ? `<p class="leading-relaxed break-words whitespace-pre-wrap">${escapeHtml(chat.message)}</p>` : ""}
                        ${chat.attachment_path
                    ? `
                            <div class="mt-3 block overflow-hidden rounded-xl border ${isMe ? "border-black/20" : "border-white/10"} relative cursor-zoom-in" onclick="window.openImageModal('/storage/${chat.attachment_path}')">
                                <img src="/storage/${chat.attachment_path}" class="w-full h-auto max-h-[200px] object-contain bg-black/10">
                            </div>`
                    : ""
                }
                    </div>
                </div>`;
        }

        chatContainer.insertAdjacentHTML("beforeend", html);
        scrollToBottom();
    };

    let rowCount = 0;

    window.unlockSpecPanel = function () {
        const designCanvasPanel = document.getElementById(
            "panel_design_canvas",
        );
        const unlockedPanel = document.getElementById("panel_unlocked");

        if (designCanvasPanel && unlockedPanel) {
            designCanvasPanel.classList.add("opacity-0", "pointer-events-none");
            setTimeout(() => {
                unlockedPanel.classList.remove(
                    "opacity-0",
                    "pointer-events-none",
                );
                unlockedPanel.classList.add("z-30");
                if (rowCount === 0) window.addNewRow();
            }, 300);
        }
    };

    window.toggleMockupPreview = function () {
        const photos = document.getElementById("mockup_preview_photos");
        const icon = document.getElementById("mockup_toggle_icon");
        if (!photos) return;

        const isHidden = photos.classList.contains("hidden");
        photos.classList.toggle("hidden", !isHidden);
        if (icon) icon.style.transform = isHidden ? "rotate(180deg)" : "";
    };

    window.addNewRow = function () {
        const rowsContainer = document.getElementById("rows_container");
        if (!rowsContainer) return;

        rowCount++;

        const rowDiv = document.createElement("div");
        rowDiv.className =
            "p-4 bg-surface-container-low border border-outline-variant/30 rounded-xl relative group shadow-md";
        rowDiv.setAttribute("data-row", rowCount);

        if (rowCount > 1) {
            const deleteBtn = document.createElement("button");
            deleteBtn.type = "button";
            deleteBtn.className =
                "absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-10";
            deleteBtn.innerHTML =
                '<span class="material-symbols-outlined text-[12px]">close</span>';
            deleteBtn.onclick = function () {
                rowDiv.remove();
                window.calculateTotal();
            };
            rowDiv.appendChild(deleteBtn);
        }

        const labelDiv = document.createElement("div");
        labelDiv.className =
            "text-[9px] uppercase tracking-widest text-primary mb-3 border-b border-outline-variant/10 pb-1";
        labelDiv.textContent = `Kombinasi #${rowCount}`;
        rowDiv.appendChild(labelDiv);

        const gridDiv = document.createElement("div");
        gridDiv.className = "flex flex-col gap-3";

        const lenganAttr = (window.appProductAttributes || []).find((a) =>
            a.name.toLowerCase().includes("lengan"),
        );

        const inventories = window.appInventories || [];
        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;
        const rc = rowCount;

        let lenganRadioHtml = "";
        if (lenganAttr) {
            const radios = lenganAttr.values
                .map(
                    (val) => `
            <label class="flex items-center gap-2 cursor-pointer group/radio">
                <input type="radio"
                    name="items[${rc}][lengan]"
                    value="${val.value}"
                    data-lengan="${val.value}"
                    onchange="window.filterVarianByLengan(${rc})"
                    class="lengan-radio accent-primary w-3.5 h-3.5 cursor-pointer">
                <span class="text-xs text-on-surface-variant group-has-[:checked]/radio:text-white transition-colors">
                    ${val.value}
                </span>
            </label>
        `,
                )
                .join("");

            lenganRadioHtml = `
            <div>
                <label class="block text-[9px] uppercase tracking-widest text-on-surface-variant mb-2">
                    Jenis Lengan <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4 flex-wrap">
                    ${radios}
                </div>
            </div>
        `;
        }

        let invSelectHtml = "";
        let invDisabledClass = "";
        let invDisabledAttr = "";

        if (lenganAttr) {
            invSelectHtml = `<option value="" data-harga="0">-- Pilih Jenis Lengan dulu --</option>`;
            invDisabledClass = "opacity-50 cursor-not-allowed";
            invDisabledAttr = "disabled";
        } else {
            let allOptions = `<option value="" data-harga="0">-- Pilih Varian --</option>`;
            inventories.forEach((inv) => {
                const hargaKain = parseFloat(inv.harga_jual) || 0;
                const totalPerPcs = hargaKain + biayaSablon;
                allOptions += `<option value="${inv.id}" data-harga="${hargaKain}">
                    ${inv.variant_name} — Rp ${totalPerPcs.toLocaleString("id-ID")}/pcs
                </option>`;
            });
            invSelectHtml = allOptions;
            invDisabledClass = "";
            invDisabledAttr = "";
        }

        gridDiv.innerHTML = `
        ${lenganRadioHtml}

        <div>
            <label class="block text-[9px] uppercase tracking-widest text-on-surface-variant mb-1">
                Varian <span class="text-red-500">*</span>
            </label>
            <select name="items[${rc}][product_inventory_id]" required
                id="inv-select-${rc}"
                onchange="window.calculateTotal()"
                class="inv-select w-full bg-background border border-outline-variant/30 text-on-surface text-xs p-2 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary appearance-none ${invDisabledClass}"
                ${invDisabledAttr}>
                ${invSelectHtml}
            </select>
        </div>

        <div>
            <label class="block text-[9px] uppercase tracking-widest text-on-surface-variant mb-1">
                Jumlah (Qty) <span class="text-red-500">*</span>
            </label>
            <input type="number"
                name="items[${rc}][qty]"
                required min="1" value="1"
                oninput="window.calculateTotal()"
                class="qty-input w-full bg-background border border-outline-variant/30 text-primary font-bold text-xs p-2 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
    `;

        rowDiv.appendChild(gridDiv);
        rowsContainer.appendChild(rowDiv);

        window.calculateTotal();
    };

    window.filterVarianByLengan = function (rc) {
        const row = document.querySelector(`[data-row="${rc}"]`);
        if (!row) return;

        const checkedRadio = row.querySelector(".lengan-radio:checked");
        if (!checkedRadio) return;

        const lenganValue = checkedRadio.getAttribute("data-lengan");
        const keyword = lenganValue.split(" ").pop().toLowerCase();

        const inventories = window.appInventories || [];

        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;

        const filtered = inventories.filter((inv) =>
            inv.variant_name.toLowerCase().includes(keyword),
        );

        const select = document.getElementById(`inv-select-${rc}`);
        if (!select) return;

        if (filtered.length === 0) {
            select.innerHTML = `<option value="" data-harga="0">-- Tidak ada varian tersedia --</option>`;
            select.disabled = true;
            select.classList.add("opacity-50", "cursor-not-allowed");
        } else {
            let options = `<option value="" data-harga="0">-- Pilih Warna & Ukuran --</option>`;
            filtered.forEach((inv) => {
                const hargaKain = parseFloat(inv.harga_jual) || 0;
                const totalPerPcs = hargaKain + biayaSablon;
                const parts = inv.variant_name.split(" - ");
                const label =
                    parts.length > 1
                        ? parts.slice(1).join(" - ")
                        : inv.variant_name;

                options += `<option value="${inv.id}" data-harga="${hargaKain}">
                ${label} — Rp ${totalPerPcs.toLocaleString("id-ID")}/pcs
                (kain Rp ${hargaKain.toLocaleString("id-ID")} + sablon Rp ${biayaSablon.toLocaleString("id-ID")})
            </option>`;
            });

            select.innerHTML = options;
            select.disabled = false;
            select.classList.remove("opacity-50", "cursor-not-allowed");
        }

        window.calculateTotal();
    };

    window.calculateTotal = function () {
        hideSpecError();
        let totalQty = 0;
        let totalHarga = 0;

        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;

        document.querySelectorAll("[data-row]").forEach((row) => {
            const qtyInput = row.querySelector(".qty-input");
            const invSelect = row.querySelector(".inv-select");

            const qty = parseInt(qtyInput?.value) || 0;
            totalQty += qty;

            const selectedOption = invSelect?.options[invSelect?.selectedIndex];
            const hargaKain =
                parseFloat(selectedOption?.getAttribute("data-harga")) || 0;

            totalHarga += (hargaKain + biayaSablon) * qty;
        });

        const totalQtyEl = document.getElementById("grand_total_qty");
        const totalHargaEl = document.getElementById("grand_total_harga");

        if (totalQtyEl)
            totalQtyEl.innerHTML = `${totalQty} <span class="text-sm">Pcs</span>`;

        if (totalHargaEl) {
            if (totalQty > 0) {
                const biayaSablonTotal = biayaSablon * totalQty;
                const hargaKainTotal = totalHarga - biayaSablonTotal;
                totalHargaEl.innerHTML = `
                Est. Rp ${totalHarga.toLocaleString("id-ID")}
                <span class="block text-[9px] text-primary/50 font-normal mt-0.5">
                    Kain Rp ${hargaKainTotal.toLocaleString("id-ID")} 
                    + Sablon Rp ${biayaSablonTotal.toLocaleString("id-ID")}
                </span>
            `;
            } else {
                totalHargaEl.textContent = "-";
            }
        }
    };

    const textareas = document.getElementsByTagName("textarea");
    for (let i = 0; i < textareas.length; i++) {
        textareas[i].setAttribute(
            "style",
            "height:" + textareas[i].scrollHeight + "px;overflow-y:hidden;",
        );
        textareas[i].addEventListener(
            "input",
            function () {
                this.style.height = "auto";
                this.style.height = this.scrollHeight + "px";
                this.style.overflowY =
                    this.scrollHeight > 80 ? "auto" : "hidden";
            },
            false,
        );
    }

    const fileInput = document.getElementById("chat_file_input");
    const previewContainer = document.getElementById(
        "chat_file_preview_container",
    );
    const previewImage = document.getElementById("chat_file_preview_image");
    const previewDoc = document.getElementById("chat_file_preview_doc");
    const removeBtn = document.getElementById("btn_remove_chat_file");

    if (fileInput) {
        fileInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                previewContainer.classList.remove("hidden");
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewImage.src = e.target.result;
                        previewImage.classList.remove("hidden");
                        if (previewDoc) previewDoc.classList.add("hidden");
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewImage.classList.add("hidden");
                    if (previewDoc) previewDoc.classList.remove("hidden");
                }
            }
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener("click", () => {
            if (fileInput) fileInput.value = "";
            previewContainer.classList.add("hidden");
            previewImage.src = "";
        });
    }

    const specForm = document.getElementById("spec_form");
    if (specForm) {
        specForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const submitBtn = document.querySelector(
                'button[type="submit"][form="spec_form"]',
            );
            const originalHtml = submitBtn?.innerHTML;

            if (submitBtn) {
                submitBtn.innerHTML =
                    '<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Menyimpan...';
                submitBtn.disabled = true;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch(
                    `/customer/progress-pesanan/${CONFIG.orderId}/specs`,
                    {
                        method: "POST",
                        headers: {
                            Authorization: "Bearer " + CONFIG.apiToken,
                            Accept: "application/json",
                            "X-CSRF-TOKEN": CONFIG.csrfToken,
                        },
                        body: formData,
                    },
                );

                const data = await res.json();

                if (res.ok) {
                    const totalHarga = data.data?.final_price
                        ? "Rp " +
                        Number(data.data.final_price).toLocaleString("id-ID")
                        : "-";

                    const totalHargaEl =
                        document.getElementById("grand_total_harga");
                    if (totalHargaEl)
                        totalHargaEl.textContent = "Est. " + totalHarga;

                    if (submitBtn) {
                        submitBtn.innerHTML =
                            '<span class="material-symbols-outlined text-[18px]">check_circle</span> Tersimpan!';
                        submitBtn.classList.remove(
                            "gold-gradient",
                            "text-on-primary",
                        );
                        submitBtn.classList.add("bg-green-500", "text-white");
                    }

                    setTimeout(() => {
                        window.fetchOrderData();
                    }, 1500);
                } else {
                    alert(
                        data.error ||
                        "Gagal menyimpan spesifikasi. Silakan coba lagi.",
                    );
                    if (submitBtn) {
                        submitBtn.innerHTML = originalHtml;
                        submitBtn.disabled = false;
                    }
                }
            } catch (err) {
                console.error("[Spec Form] Error:", err);
                alert("Koneksi terputus. Gagal menyimpan spesifikasi.");
                if (submitBtn) {
                    submitBtn.innerHTML = originalHtml;
                    submitBtn.disabled = false;
                }
            }
        });
    }

    window.approveDesign = function () {
        const modal = document.getElementById("modal_approve_design");
        if (modal) modal.classList.remove("hidden");
    };

    window.confirmApproveDesign = async function () {
        const btn = document.getElementById("btn_confirm_approve");
        const originalText = btn.innerHTML;

        btn.innerHTML =
            '<span class="material-symbols-outlined animate-spin text-[16px] align-middle mr-2">sync</span> Memproses...';
        btn.disabled = true;

        try {
            const res = await fetch(
                `/customer/order/${CONFIG.orderId}/approve-design`,
                {
                    method: "POST",
                    headers: {
                        Authorization: "Bearer " + CONFIG.apiToken,
                        Accept: "application/json",
                        "X-CSRF-TOKEN": CONFIG.csrfToken,
                    },
                },
            );

            if (res.ok) {
                document
                    .getElementById("modal_approve_design")
                    .classList.add("hidden");

                const successModal = document.getElementById(
                    "modal_success_approve",
                );
                if (successModal) successModal.classList.remove("hidden");

                window.fetchOrderData();
            } else {
                console.warn("[Network] Gagal memproses persetujuan.");
            }
        } catch (e) {
            console.error("[System] Gagal menyetujui desain:", e);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    const chatForm = document.getElementById("chat_form");
    if (chatForm) {
        chatForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const messageInput = this.querySelector('textarea[name="message"]');
            const hasText = messageInput && messageInput.value.trim() !== "";
            const hasFile = fileInput && fileInput.files.length > 0;

            if (!hasText && !hasFile) return;

            const btn = document.getElementById("btn_send_chat");
            let originalIcon = "";
            if (btn) {
                originalIcon = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="material-symbols-outlined animate-spin">sync</span>';
            }

            const formData = new FormData(this);
            const requestHeaders = {
                Authorization: "Bearer " + CONFIG.apiToken,
                Accept: "application/json",
                "X-CSRF-TOKEN": CONFIG.csrfToken,
            };

            const socketId = window.Echo ? window.Echo.socketId() : null;
            if (socketId) requestHeaders["X-Socket-ID"] = socketId;

            try {
                const res = await fetch(this.action, {
                    method: "POST",
                    headers: requestHeaders,
                    body: formData,
                });

                if (res.ok) {
                    const data = await res.json();
                    this.reset();
                    if (messageInput) messageInput.style.height = "auto";
                    if (previewContainer)
                        previewContainer.classList.add("hidden");

                    if (data.chat) window.appendSingleMessage(data.chat, true);
                } else {
                    console.warn(
                        "[Network] Pengiriman pesan gagal, status:",
                        res.status,
                    );
                }
            } catch (e) {
                alert("Koneksi terputus. Gagal mengirim pesan.");
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalIcon;
                }
            }
        });
    }

    const pendingConfirmId = sessionStorage.getItem("pending_payment_confirm");

    if (
        pendingConfirmId &&
        String(pendingConfirmId) === String(CONFIG.orderId)
    ) {
        console.info(
            "[System] Mengkonfirmasi pembayaran setelah redirect Midtrans...",
        );

        const pageLoader = document.getElementById("page_loader");
        if (pageLoader) {
            pageLoader.classList.remove("hidden");
            const loaderText = pageLoader.querySelector("p");
            if (loaderText)
                loaderText.textContent = "Mengkonfirmasi pembayaran...";
        }

        fetch(`/customer/order/${CONFIG.orderId}/confirm-payment`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer " + CONFIG.apiToken,
                Accept: "application/json",
                "X-CSRF-TOKEN": CONFIG.csrfToken,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                console.info("[Payment] Konfirmasi response:", data);
                sessionStorage.removeItem("pending_payment_confirm");
                window.fetchOrderData();
            })
            .catch((err) => {
                console.warn("[Payment] Gagal konfirmasi:", err);
                sessionStorage.removeItem("pending_payment_confirm");
                window.fetchOrderData();
            });
    } else {
        window.fetchOrderData();
    }
    window.sendReadReceipt();

    console.info("[System] Menginisialisasi koneksi Reverb (Customer)...");

    const checkEchoReady = setInterval(() => {
        if (typeof window.Echo !== "undefined" && CONFIG.orderId) {
            clearInterval(checkEchoReady);

            if (!CONFIG.apiToken) {
                console.warn(
                    "[System] JWT Token kosong. Mode Real-time nonaktif.",
                );
                return;
            }

            console.info(
                `[WebSocket] Customer memantau channel: private-order.chat.${CONFIG.orderId}`,
            );

            window.Echo.leave(`order.chat.${CONFIG.orderId}`);
            window.Echo.private(`order.chat.${CONFIG.orderId}`)
                .listen(".message.new", (e) => {
                    console.info("[WebSocket] Pesan dari Admin diterima.");

                    if (
                        String(e.message.user_id) !==
                        String(CONFIG.currentUserId)
                    ) {
                        if (typeof window.appendSingleMessage === "function") {
                            window.appendSingleMessage(e.message, true);
                        }
                        window.sendReadReceipt();
                    }
                })
                .listen(".messages.read", (e) => {
                    console.info("[WebSocket] Admin telah membaca pesan Anda.");
                    document
                        .querySelectorAll(".read-tick:not(.text-blue-500)")
                        .forEach((tick) => {
                            tick.classList.add("text-blue-500");
                            tick.classList.remove("text-inherit", "opacity-70");
                        });
                })
                .listen(".order.state.updated", (e) => {
                    console.info(
                        "[WebSocket] Memperbarui status/mockup (Aksi Admin)...",
                    );
                    window.fetchOrderData();
                });
        }
    }, 1000);

    window.showPaymentSummary = function () {
        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;
        let rows = "";
        let grandTotal = 0;
        let hasInvalidRow = false;

        const dataRows = document.querySelectorAll("[data-row]");

        dataRows.forEach((row) => {
            const qtyInput = row.querySelector(".qty-input");
            const invSelect = row.querySelector(".inv-select");
            const selectedOpt = invSelect?.options[invSelect?.selectedIndex];
            const qty = parseInt(qtyInput?.value) || 0;

            if (!selectedOpt?.value || qty < 1) {
                hasInvalidRow = true;
                return;
            }

            const hargaKain =
                parseFloat(selectedOpt?.getAttribute("data-harga")) || 0;
            const hargaPcs = hargaKain + biayaSablon;
            const subtotal = hargaPcs * qty;
            grandTotal += subtotal;

            const label = selectedOpt.text.split("—")[0].trim();

            rows += `
            <div class="flex items-start justify-between py-3 border-b border-white/5 last:border-0">
                <div class="flex-1">
                    <p class="text-xs font-bold text-white">${label}</p>
                    <p class="text-[10px] text-on-surface-variant mt-0.5">
                        Rp ${hargaKain.toLocaleString("id-ID")} + Sablon Rp ${biayaSablon.toLocaleString("id-ID")} = 
                        <span class="text-primary">Rp ${hargaPcs.toLocaleString("id-ID")}/pcs</span>
                    </p>
                </div>
                <div class="text-right ml-4">
                    <p class="text-xs text-on-surface-variant">${qty} pcs</p>
                    <p class="text-sm font-black text-white">Rp ${subtotal.toLocaleString("id-ID")}</p>
                </div>
            </div>
        `;
        });

        if (dataRows.length === 0 || hasInvalidRow) {
            showSpecError("Pastikan setiap kombinasi sudah memilih varian dan jumlah minimal 1 pcs.");
            return;
        }
        hideSpecError();

        const dpAmount = Math.round(grandTotal * 0.5);

        document.getElementById("payment_summary_rows").innerHTML = rows;
        document.getElementById("payment_grand_total").textContent =
            "Rp " + grandTotal.toLocaleString("id-ID");
        document.getElementById("payment_dp_amount").textContent =
            "Rp " + dpAmount.toLocaleString("id-ID");
        document.getElementById("payment_modal").classList.remove("hidden");
    };

    window.processPayment = async function (paymentType) {
        const btn = document.getElementById("btn_pay_" + paymentType);
        const originalHtml = btn?.innerHTML;

        if (btn) {
            btn.innerHTML =
                '<span class="material-symbols-outlined animate-spin text-[16px]">sync</span> Memproses...';
            btn.disabled = true;
        }

        const biayaSablon = parseFloat(orderData?.biaya_sablon) || 0;
        let grandTotal = 0;
        let hasInvalidRow = false;

        const dataRows = document.querySelectorAll("[data-row]");
        dataRows.forEach((row) => {
            const qtyInput = row.querySelector(".qty-input");
            const invSelect = row.querySelector(".inv-select");
            const selectedOpt = invSelect?.options[invSelect?.selectedIndex];
            const qty = parseInt(qtyInput?.value) || 0;

            if (!selectedOpt?.value || qty < 1) {
                hasInvalidRow = true;
                return;
            }

            const hargaKain =
                parseFloat(selectedOpt?.getAttribute("data-harga")) || 0;
            grandTotal += (hargaKain + biayaSablon) * qty;
        });

        if (dataRows.length === 0 || hasInvalidRow || grandTotal <= 0) {
            showSpecError("Pastikan setiap kombinasi sudah memilih varian dan jumlah minimal 1 pcs.");
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
            return;
        }
        hideSpecError();

        try {
            const specForm = document.getElementById("spec_form");
            const formData = new FormData(specForm);

            const specRes = await fetch(
                `/customer/progress-pesanan/${CONFIG.orderId}/specs`,
                {
                    method: "POST",
                    headers: {
                        Authorization: "Bearer " + CONFIG.apiToken,
                        Accept: "application/json",
                        "X-CSRF-TOKEN": CONFIG.csrfToken,
                    },
                    body: formData,
                },
            );

            const specData = await specRes.json();
            if (!specRes.ok) {
                alert(specData.error || "Gagal menyimpan spesifikasi.");
                if (btn) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
                return;
            }

            const payRes = await fetch(
                `/customer/order/${CONFIG.orderId}/snap-token`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: "Bearer " + CONFIG.apiToken,
                        Accept: "application/json",
                        "X-CSRF-TOKEN": CONFIG.csrfToken,
                    },
                    body: JSON.stringify({
                        payment_type: paymentType,
                        final_price: grandTotal,
                    }),
                },
            );

            const payData = await payRes.json();
            if (!payRes.ok) {
                alert(payData.error || "Gagal memproses pembayaran.");
                if (btn) {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
                return;
            }

            document.getElementById("payment_modal").classList.add("hidden");

            window.snap.pay(payData.snap_token, {
                onSuccess: async function (result) {
                    console.log("[Midtrans] Berhasil:", result);
                    sessionStorage.setItem(
                        "pending_payment_confirm",
                        CONFIG.orderId,
                    );

                    try {
                        const confirmRes = await fetch(
                            `/customer/order/${CONFIG.orderId}/confirm-payment`,
                            {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    Authorization: "Bearer " + CONFIG.apiToken,
                                    Accept: "application/json",
                                    "X-CSRF-TOKEN": CONFIG.csrfToken,
                                },
                            },
                        );
                        const confirmData = await confirmRes.json();
                        console.log("[Payment] Konfirmasi:", confirmData);
                        sessionStorage.removeItem("pending_payment_confirm");
                    } catch (e) {
                        console.warn(
                            "[Payment] Redirect terjadi, akan dikonfirmasi saat reload.",
                        );
                    }
                    window.fetchOrderData();
                },
                onPending: function (result) {
                    console.log("[Midtrans] Pending:", result);
                    sessionStorage.setItem(
                        "pending_payment_confirm",
                        CONFIG.orderId,
                    );
                },
                onError: function (result) {
                    console.error("[Midtrans] Error:", result);
                    alert("Pembayaran gagal. Silakan coba lagi.");
                },
                onClose: function () {
                    console.log("[Midtrans] Ditutup oleh user.");
                    if (sessionStorage.getItem("pending_payment_confirm")) {
                        window.location.reload();
                    }
                },
            });
        } catch (err) {
            console.error("[Payment] Error:", err);
            alert("Koneksi terputus. Gagal memproses pembayaran.");
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    };
});
