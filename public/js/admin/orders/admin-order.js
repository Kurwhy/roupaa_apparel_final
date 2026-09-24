function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

document.addEventListener("DOMContentLoaded", () => {
    const inputSablon = document.getElementById("input_biaya_sablon");
    const previewSablon = document.getElementById("preview_sablon");
    if (inputSablon && previewSablon) {
        function updatePreviewSablon() {
            const val = parseInt(inputSablon.value) || 0;
            previewSablon.textContent = val.toLocaleString("id-ID");
        }
        inputSablon.addEventListener("input", updatePreviewSablon);
        updatePreviewSablon();
    }

    const CONFIG = window.APP_CONFIG;

    const orderId = window.ORDER_ID;
    if (orderId && CONFIG.apiToken) {
        fetch(`/ops/api/notifications/order/${orderId}/read`, {
            method: "POST",
            headers: {
                Authorization: "Bearer " + CONFIG.apiToken,
                "X-CSRF-TOKEN": CONFIG.csrfToken,
                Accept: "application/json",
            },
        });
    }

    const initiatedEl = document.getElementById('ui_order_initiated');
    if (initiatedEl && initiatedEl.dataset.date) {
        initiatedEl.textContent = 'Diinisiasi: ' + formatDate(initiatedEl.dataset.date);
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

    function scrollToBottom() {
        const chatArea = document.getElementById("chat_area");
        if (chatArea) chatArea.scrollTop = chatArea.scrollHeight;
    }
    setTimeout(scrollToBottom, 100);
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

    window.closeImageModal = function () {
        const modal = document.getElementById("image_modal");
        if (modal) {
            modal.classList.add("opacity-0");
            modal.children[1].children[0].classList.add("scale-95");
            setTimeout(() => {
                modal.classList.add("hidden");
                document.getElementById("modal_image_content").src = "";
            }, 300);
        }
    };

    window.appendSingleMessage = function (chat, isInstant = true) {
        const chatArea = document.getElementById("chat_area");
        if (!chatArea) return;

        const isMe = String(chat.user_id) === String(CONFIG.currentUserId);

        const isStaffMessage = chat.user
            ? (chat.user.role === "admin" || chat.user.role === "owner")
            : isMe;

        let timeStr = "";
        try {
            timeStr = chat.created_at
                ? (formatDate(chat.created_at).split(",")[1] || "")
                : new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
        } catch (_) { }

        let senderLabel;
        if (isMe) {
            senderLabel = chat.user?.role === "owner" ? "Anda (Owner)" : "Anda (Admin)";
        } else if (isStaffMessage) {
            senderLabel = chat.user?.role === "owner" ? "Owner" : "Admin";
        } else {
            senderLabel = "Pelanggan";
        }

        let tickHtml = "";
        if (isStaffMessage) {
            const tickColor =
                chat.read_at && !isInstant
                    ? "text-blue-500"
                    : "text-inherit opacity-70";
            tickHtml = `<span class="material-symbols-outlined text-[13px] ml-1 read-tick ${tickColor}">done_all</span>`;
        }

        let attachmentHtml = "";
        if (chat.attachment_path) {
            const fileUrl = `/storage/${chat.attachment_path}`;
            attachmentHtml = `
            <div class="mt-3 block overflow-hidden rounded-xl border ${isStaffMessage ? "border-black/20" : "border-white/10"} relative cursor-zoom-in" onclick="window.openImageModal('${fileUrl}')">
                <img src="${fileUrl}" class="w-full h-auto max-h-[200px] object-contain bg-black/10">
            </div>`;
        }

        let bubbleHtml = "";
        if (chat.is_system_message) {
            bubbleHtml = `
            <div class="flex justify-center my-2">
                <span class="bg-surface border border-white/10 text-on-surface-variant text-[10px] px-4 py-2 rounded-full font-medium shadow-sm">
                    <span class="material-symbols-outlined text-[12px] align-middle mr-1">info</span> ${escapeHtml(chat.message)}
                </span>
            </div>`;
        } else {
            bubbleHtml = `
            <div class="flex flex-col ${isStaffMessage ? "items-end" : "items-start"} w-full group/chat">
                <div class="flex items-center gap-2 mb-1.5 px-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider ${isStaffMessage ? "text-primary" : "text-white"}">
                        ${senderLabel}
                    </span>
                    <span class="text-[9px] text-on-surface-variant/50 flex items-center">
                        ${timeStr} ${tickHtml}
                    </span>
                </div>
                <div class="max-w-[75%] w-fit min-w-[48px] p-4 text-sm shadow-md relative ${isStaffMessage ? "bg-primary text-black rounded-2xl rounded-tr-sm" : "bg-surface border border-white/10 text-white rounded-2xl rounded-tl-sm"}">
                    ${chat.message ? `<p class="leading-relaxed break-words whitespace-pre-wrap">${escapeHtml(chat.message)}</p>` : ""}
                    ${attachmentHtml}
                </div>
            </div>`;
        }

        chatArea.insertAdjacentHTML("beforeend", bubbleHtml);
        scrollToBottom();
    };
    (function () {
        var chats = window.INITIAL_CHATS || [];
        if (!chats.length) {
            var chatArea = document.getElementById("chat_area");
            if (chatArea)
                chatArea.insertAdjacentHTML(
                    "beforeend",
                    `
                <div class="m-auto text-center flex flex-col items-center justify-center h-full opacity-50">
                    <div class="w-16 h-16 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl text-white">chat_bubble</span>
                    </div>
                    <h4 class="text-white font-bold mb-1">Belum Ada Percakapan</h4>
                    <p class="text-xs text-on-surface-variant max-w-xs">Kirim pesan pertama untuk memulai diskusi.</p>
                </div>`,
                );
            return;
        }
        chats.forEach(function (chat) {
            appendSingleMessage(chat, false);
        });
    })();

    window.refreshStateUI = async function () {
        try {
            const res = await fetch(window.location.href, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Authorization: "Bearer " + CONFIG.apiToken,
                },
            });
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, "text/html");

            [
                "panel_design_canvas",
                "panel_unlocked",
                "panel_tindakan_admin",
                "status_berjalan_badge",
            ].forEach((id) => {
                const oldEl = document.getElementById(id);
                const newEl = doc.getElementById(id);
                if (oldEl && newEl) oldEl.outerHTML = newEl.outerHTML;
            });

            document.getElementById("modal_revisi")?.classList.add("hidden");
            window.initMockupUpload?.();
            window.initProductionPhotoUpload?.();
        } catch (err) {
            console.error("[UI] Gagal sinkronasi:", err);
        }
    };

    window.initMockupUpload = function () {
        const mockupUpload = document.getElementById("mockup_upload");
        const previewContainer = document.getElementById(
            "image_preview_container",
        );
        const btnSubmit = document.getElementById("btn-submit-mockup");
        let selectedFiles = [];

        if (!mockupUpload || !previewContainer) return;

        mockupUpload.addEventListener("click", function () {
            this.value = null;
        });

        mockupUpload.addEventListener("change", function (event) {
            Array.from(event.target.files).forEach((file) => {
                if (file.type.startsWith("image/")) selectedFiles.push(file);
            });
            updatePreview();
        });

        function updatePreview() {
            previewContainer.innerHTML = "";
            if (selectedFiles.length > 0) {
                previewContainer.classList.remove("hidden");
                previewContainer.classList.add("flex");
                if (btnSubmit) btnSubmit.disabled = false;
            } else {
                previewContainer.classList.add("hidden");
                previewContainer.classList.remove("flex");
                if (btnSubmit) btnSubmit.disabled = true;
            }

            const dataTransfer = new DataTransfer();
            selectedFiles.forEach((file, index) => {
                dataTransfer.items.add(file);
                const reader = new FileReader();
                reader.onload = function (e) {
                    const wrapper = document.createElement("div");
                    wrapper.className =
                        "relative inline-block flex-shrink-0 mr-3 mb-3";
                    wrapper.innerHTML = `
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden border border-white/20 bg-surface-container-highest shadow-md">
                            <img src="${e.target.result}" class="w-full h-full object-cover cursor-zoom-in hover:brightness-75 transition-all" onclick="window.openImageModal('${e.target.result}')">
                        </div>
                        <button type="button" class="btn-delete-photo absolute -top-2 -right-2 w-6 h-6 bg-surface-container-high border border-white/20 text-on-surface-variant rounded-full flex items-center justify-center hover:text-white hover:bg-red-500 hover:border-red-500 transition-all shadow-lg z-10" data-index="${index}">
                            <span class="material-symbols-outlined text-[12px]">close</span>
                        </button>`;
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
            mockupUpload.files = dataTransfer.files;
        }

        previewContainer.addEventListener("click", function (e) {
            const deleteBtn = e.target.closest(".btn-delete-photo");
            if (deleteBtn) {
                selectedFiles.splice(parseInt(deleteBtn.dataset.index), 1);
                updatePreview();
            }
        });

        if (btnSubmit) btnSubmit.disabled = true;
    };
    window.initMockupUpload();

    const textareas = document.getElementsByTagName("textarea");
    for (let i = 0; i < textareas.length; i++) {
        textareas[i].style.height = textareas[i].scrollHeight + "px";
        textareas[i].style.overflowY = "hidden";
        textareas[i].addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
            this.style.overflowY = this.scrollHeight > 80 ? "auto" : "hidden";
        });
    }

    const fileInputChat = document.getElementById("chat_file_input");
    const previewContainerChat = document.getElementById(
        "chat_file_preview_container",
    );
    const previewImageChat = document.getElementById("chat_file_preview_image");
    const previewDocChat = document.getElementById("chat_file_preview_doc");
    const removeFileBtnChat = document.getElementById("btn_remove_chat_file");

    if (fileInputChat) {
        fileInputChat.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                previewContainerChat.classList.remove("hidden");
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewImageChat.src = e.target.result;
                        previewImageChat.classList.remove("hidden");
                        previewDocChat?.classList.add("hidden");
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewImageChat.classList.add("hidden");
                    previewDocChat?.classList.remove("hidden");
                }
            } else {
                previewContainerChat.classList.add("hidden");
            }
        });
    }

    if (removeFileBtnChat) {
        removeFileBtnChat.addEventListener("click", function () {
            if (fileInputChat) fileInputChat.value = "";
            previewContainerChat.classList.add("hidden");
            previewImageChat.src = "";
        });
    }

    document.addEventListener("submit", function (e) {
        const form = e.target;
        if (form.id === "chat_form") return;

        const isActionForm = [
            "/mockup",
            "/lanjutkan",
            "/approve-design",
            "/selesai-produksi",
            "/selesai-kirim",
        ].some((path) => form.action.includes(path));

        if (!isActionForm) return;

        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        let originalHtml = "";
        if (submitBtn) {
            originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML =
                '<span class="material-symbols-outlined">sync</span> Memproses...';
            submitBtn.disabled = true;
        }

        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
                Authorization: "Bearer " + CONFIG.apiToken,
                "X-CSRF-TOKEN": CONFIG.csrfToken,
            },
        })
            .then(() => window.refreshStateUI?.())
            .catch((err) => {
                console.error("[Form] Gagal:", err);
                alert("Terjadi kesalahan. Silakan muat ulang halaman.");
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.innerHTML = originalHtml;
                    submitBtn.disabled = false;
                }
            });
    });

    const chatForm = document.getElementById("chat_form");
    if (chatForm) {
        chatForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const messageInput = this.querySelector('textarea[name="message"]');
            const messageText = messageInput?.value.trim() ?? "";
            const fileCount = fileInputChat?.files?.length ?? 0;
            if (!messageText && fileCount === 0) return;

            const submitBtn = this.querySelector('button[type="submit"]');
            let originalIcon = "";
            if (submitBtn) {
                originalIcon = submitBtn.innerHTML;
                submitBtn.innerHTML =
                    '<span class="material-symbols-outlined text-[20px]">sync</span>';
                submitBtn.disabled = true;
            }

            const headers = {
                "X-CSRF-TOKEN": CONFIG.csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
                Authorization: "Bearer " + CONFIG.apiToken,
            };
            const socketId = window.Echo?.socketId();
            if (socketId) headers["X-Socket-ID"] = socketId;

            try {
                const res = await fetch(this.action, {
                    method: "POST",
                    body: new FormData(this),
                    headers,
                });

                if (res.ok) {
                    const data = await res.json();
                    if (messageInput) {
                        messageInput.value = "";
                        messageInput.style.height = "auto";
                    }
                    if (fileInputChat) fileInputChat.value = "";
                    previewContainerChat?.classList.add("hidden");
                    if (previewImageChat) previewImageChat.src = "";
                    if (data.chat) window.appendSingleMessage(data.chat, true);
                }
            } catch (err) {
                console.error("[Chat] Gagal kirim:", err);
            } finally {
                if (submitBtn) {
                    submitBtn.innerHTML = originalIcon;
                    submitBtn.disabled = false;
                }
            }
        });
    }

    const checkEcho = setInterval(() => {
        if (typeof window.Echo !== "undefined" && CONFIG.orderId) {
            clearInterval(checkEcho);
            if (!CONFIG.apiToken) return;

            window.Echo.leave(`order.chat.${CONFIG.orderId}`);
            window.Echo.private(`order.chat.${CONFIG.orderId}`)
                .listen(".message.new", (e) => {
                    if (String(e.message.user_id) !== String(CONFIG.currentUserId)) {
                        window.appendSingleMessage?.(e.message, true);
                        window.sendReadReceipt?.();
                        window.playNotifSound?.();
                    }
                })
                .listen(".messages.read", () => {
                    document
                        .querySelectorAll(".read-tick:not(.text-blue-500)")
                        .forEach((tick) => {
                            tick.classList.add("text-blue-500");
                            tick.classList.remove("text-inherit", "opacity-70");
                        });
                })
                .listen(".order.state.updated", () => {
                    window.refreshStateUI?.();
                });
        }
    }, 1000);
    window.sendReadReceipt();

    const formEstimasi = document.getElementById("form_estimasi_harga");
    if (formEstimasi) {
        const summaryBaju = document.getElementById("summary_baju");
        const summarySablon = document.getElementById("summary_sablon");
        const summaryTotal = document.getElementById("summary_total");

        function formatRp(n) {
            return "Rp " + Math.round(n).toLocaleString("id-ID");
        }

        function hitungUlang() {
            let totalBaju = 0,
                totalSablon = 0;
            formEstimasi.querySelectorAll("[data-item-row]").forEach((row) => {
                const hargaBaju = parseFloat(row.dataset.hargaBaju) || 0;
                const qty = parseInt(row.dataset.qty) || 1;
                const biayaSablon =
                    parseFloat(row.querySelector(".sablon-input")?.value) || 0;
                const subtotal = (hargaBaju + biayaSablon) * qty;
                const cell = row.querySelector(".subtotal-item");
                if (cell) cell.textContent = formatRp(subtotal);
                totalBaju += hargaBaju * qty;
                totalSablon += biayaSablon * qty;
            });
            if (summaryBaju) summaryBaju.textContent = formatRp(totalBaju);
            if (summarySablon)
                summarySablon.textContent = formatRp(totalSablon);
            if (summaryTotal)
                summaryTotal.textContent = formatRp(totalBaju + totalSablon);
        }

        formEstimasi
            .querySelectorAll(".sablon-input")
            .forEach((input) => input.addEventListener("input", hitungUlang));
        hitungUlang();

        formEstimasi.addEventListener("submit", function (e) {
            const total = summaryTotal?.textContent ?? "?";
            if (
                !confirm(
                    `Kirim estimasi ke customer?\n\nTotal: ${total}\n\nCustomer akan mendapat notifikasi via chat.`,
                )
            )
                e.preventDefault();
        });
    }

    window.initProductionPhotoUpload = function () {
        const photoUpload = document.getElementById("production_photo_upload");
        const previewContainer = document.getElementById(
            "production_preview_container",
        );
        const btnComplete = document.getElementById("btn_mark_complete");

        if (!photoUpload || !previewContainer) return;

        photoUpload.addEventListener("change", function () {
            const files = Array.from(this.files);
            previewContainer.innerHTML = "";

            if (files.length > 0) {
                previewContainer.classList.remove("hidden");
                previewContainer.classList.add("flex");
                if (btnComplete) btnComplete.disabled = false;

                files.forEach((file) => {
                    if (!file.type.startsWith("image/")) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const wrapper = document.createElement("div");
                        wrapper.className =
                            "w-20 h-20 rounded-xl overflow-hidden border border-white/20 shadow-md flex-shrink-0";
                        wrapper.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover cursor-zoom-in" onclick="window.openImageModal('${e.target.result}')">`;
                        previewContainer.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                previewContainer.classList.add("hidden");
                if (btnComplete) btnComplete.disabled = true;
            }
        });
    };
    window.initProductionPhotoUpload();
});
