document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("btn_generate");
    const placeholder = document.getElementById("ai_result_placeholder");
    const wrapper = document.getElementById("ai_image_wrapper");
    const img = document.getElementById("ai_generated_image");
    const promptInput = document.getElementById("prompt_input");
    const creditCounter = document.getElementById("credit_counter");
    const creditDot = document.getElementById("credit_dot");
    const historyContainer = document.getElementById(
        "design_history_container",
    );
    const emptyHistory = document.getElementById("empty_history");

    if (!btn) return;

    const token = sessionStorage.getItem("access_token");

    const charCounter = document.getElementById("char_counter");
    if (promptInput && charCounter) {
        promptInput.addEventListener("input", () => {
            const len = promptInput.value.length;
            charCounter.textContent = len + " / 1000";
            charCounter.style.color =
                len > 900 ? "#f87171" : len > 5 ? "#f2ca50" : "";
        });
    }

    let isGenerating = false;

    const LOADING_STEPS = [
        {
            icon: "psychology",
            text: "Memproses prompt Anda...",
            sub: "AI sedang memahami konsep desain",
        },
        {
            icon: "palette",
            text: "Melukis desain...",
            sub: "Proses ini memakan 15–30 detik",
        },
        {
            icon: "auto_awesome",
            text: "Finishing touches...",
            sub: "Hampir selesai!",
        },
    ];

    function showToast(type, message, duration = 5000) {
        document.getElementById("ai-toast")?.remove();

        const isError = type === "error";
        const toast = document.createElement("div");
        toast.id = "ai-toast";
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            max-width: 380px; padding: 14px 18px; border-radius: 16px;
            display: flex; align-items: flex-start; gap: 12px;
            font-family: 'Inter', sans-serif; font-size: 13px; line-height: 1.5;
            animation: slideUp .3s cubic-bezier(.22,.68,0,1.2) forwards;
            ${
                isError
                    ? "background:#1a0a0a;border:1px solid rgba(239,68,68,.3);color:#fca5a5;"
                    : "background:#0a1a0a;border:1px solid rgba(34,197,94,.3);color:#86efac;"
            }
        `;

        toast.innerHTML = `
            <span class="material-symbols-outlined" style="font-size:18px;flex-shrink:0;margin-top:1px">
                ${isError ? "error" : "check_circle"}
            </span>
            <span style="flex:1">${message}</span>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;padding:0;color:inherit;opacity:.6;font-size:18px;line-height:1;flex-shrink:0">×</button>
        `;

        if (!document.getElementById("toast-style")) {
            const s = document.createElement("style");
            s.id = "toast-style";
            s.textContent = `@keyframes slideUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}`;
            document.head.appendChild(s);
        }

        document.body.appendChild(toast);
        if (duration > 0) setTimeout(() => toast?.remove(), duration);
    }

    function setLoading(on) {
        isGenerating = on;
        btn.disabled = on;

        if (on) {
            btn.innerHTML = `
                <span class="material-symbols-outlined text-sm animate-spin">sync</span>
                Merancang...
            `;
        } else {
            btn.innerHTML = `
                <span class="material-symbols-outlined text-[18px]">magic_button</span>
                Generate Desain
            `;
        }
    }

    let stepInterval = null;
    function startLoadingCanvas() {
        wrapper.classList.add("hidden");
        placeholder.classList.remove("hidden");

        let step = 0;
        renderLoadingStep(step);

        stepInterval = setInterval(() => {
            step = Math.min(step + 1, LOADING_STEPS.length - 1);
            renderLoadingStep(step);
        }, 8000);
    }

    function renderLoadingStep(idx) {
        const s = LOADING_STEPS[idx];
        placeholder.innerHTML = `
            <div style="display:flex;flex-direction:column;align-items:center;gap:16px;padding:24px">
                <div style="position:relative">
                    <div style="width:64px;height:64px;border-radius:50%;border:3px solid rgba(242,202,80,.15);border-top-color:#f2ca50;animation:spin .9s linear infinite"></div>
                    <span class="material-symbols-outlined" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#f2ca50;font-size:22px">${s.icon}</span>
                </div>
                <div style="text-align:center">
                    <p style="font-family:'Space Grotesk',sans-serif;font-weight:900;font-size:13px;letter-spacing:.15em;text-transform:uppercase;color:#f2ca50;margin:0 0 6px">${s.text}</p>
                    <p style="font-size:11px;color:rgba(208,197,175,.5);margin:0">${s.sub}</p>
                </div>
                <div style="display:flex;gap:6px;margin-top:4px">
                    ${LOADING_STEPS.map(
                        (_, i) => `
                        <span style="width:${i === idx ? "20" : "6"}px;height:6px;border-radius:3px;background:${i === idx ? "#f2ca50" : "rgba(242,202,80,.2)"};transition:all .4s"></span>
                    `,
                    ).join("")}
                </div>
            </div>
        `;
    }

    function stopLoadingCanvas() {
        if (stepInterval) {
            clearInterval(stepInterval);
            stepInterval = null;
        }
    }

    function resetCanvas() {
        stopLoadingCanvas();
        placeholder.innerHTML = `
            <div class="w-16 h-16 rounded-full bg-surface-container-high border border-outline-variant/30 flex items-center justify-center mx-auto mb-4 shadow-inner">
                <span class="material-symbols-outlined text-3xl text-on-surface-variant">draw</span>
            </div>
            <h2 class="font-headline font-black text-xl text-white uppercase mb-2 tracking-widest">Visualisasikan Idenya</h2>
            <p class="text-xs text-on-surface-variant max-w-sm mx-auto opacity-70 leading-relaxed">Hasil generate AI akan muncul di sini.</p>
        `;
        placeholder.classList.remove("hidden");
        wrapper.classList.add("hidden");
    }

    function updateCredits(left) {
        if (!creditCounter) return;
        creditCounter.textContent = `${left} Kredit Hari Ini`;

        if (left <= 0) {
            creditCounter.classList.remove("text-white");
            creditCounter.classList.add("text-red-400");
            if (creditDot) {
                creditDot.classList.remove("bg-primary", "animate-pulse");
                creditDot.classList.add("bg-red-500");
            }
            setButtonDisabled();
        }
    }

    function setButtonDisabled() {
        btn.disabled = true;
        btn.className =
            btn.className.replace(/gold-gradient|hover:[^ ]+/g, "").trim() +
            " bg-surface-container-high text-white/30 cursor-not-allowed";
        btn.innerHTML = `<span class="material-symbols-outlined text-[18px]">block</span> Kuota Habis`;
    }

    function addToHistory(url, promptText, createdAt = "") {
        emptyHistory?.remove();

        const historyItem = document.createElement("img");
        historyItem.src = url;
        historyItem.title =
            (createdAt ? "[" + createdAt + "] " : "") + promptText;
        historyItem.className =
            "w-16 h-16 rounded-xl object-cover border border-white/10 cursor-pointer hover:border-primary hover:scale-105 transition-all shadow-md flex-shrink-0";
        historyItem.addEventListener("click", function () {
            img.src = this.src;
            wrapper.classList.remove("hidden");
            placeholder.classList.add("hidden");
        });

        historyContainer.prepend(historyItem);
    }

    btn.addEventListener("click", async () => {
        if (isGenerating) return;

        const basePrompt = promptInput.value.trim();
        if (basePrompt.length < 5) {
            showToast(
                "error",
                "Deskripsi terlalu singkat! Ceritakan detail desain yang diinginkan.",
            );
            promptInput.focus();
            return;
        }

        if (!token) {
            showToast(
                "error",
                "Sesi Anda telah berakhir. Halaman akan direfresh untuk login ulang.",
            );
            setTimeout(() => (window.location.href = "/login"), 2000);
            return;
        }

        setLoading(true);
        startLoadingCanvas();

        try {
            const res = await fetch(window.aiGenerateUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: "Bearer " + token,
                    "X-CSRF-TOKEN": window.csrfToken,
                },
                body: JSON.stringify({ prompt: basePrompt }),
            });

            const data = await res.json();

            if (res.ok && data.status === "success") {
                img.src = data.image_url;
                img.onload = () => {
                    stopLoadingCanvas();
                    placeholder.classList.add("hidden");
                    wrapper.classList.remove("hidden");
                    addToHistory(data.image_url, basePrompt);
                    updateCredits(data.credits_left);
                    showToast(
                        "success",
                        'Desain berhasil dibuat! Klik "Gunakan Desain" untuk melanjutkan ke order.',
                        4000,
                    );
                };
                img.onerror = () => {
                    resetCanvas();
                    showToast(
                        "error",
                        "Gambar gagal dimuat. Coba generate ulang.",
                    );
                };
            } else if (res.status === 401) {
                resetCanvas();
                sessionStorage.removeItem("access_token");
                showToast(
                    "error",
                    "Sesi berakhir. Mengalihkan ke halaman login...",
                );
                setTimeout(() => (window.location.href = "/login"), 2500);
            } else if (res.status === 403) {
                resetCanvas();
                showToast(
                    "error",
                    data.message ||
                        "Kuota harian habis. Kembali besok untuk 3 kredit baru.",
                    8000,
                );
                setButtonDisabled();
            } else if (res.status === 504) {
                resetCanvas();
                showToast(
                    "error",
                    "Server AI timeout. Ini normal saat sibuk — coba lagi dalam 30 detik.",
                    10000,
                );
            } else {
                resetCanvas();
                showToast(
                    "error",
                    data.message || "Terjadi kesalahan. Coba lagi.",
                    8000,
                );
            }
        } catch (networkErr) {
            console.error("[AI Studio] Network error:", networkErr);
            resetCanvas();
            showToast(
                "error",
                "Gagal terhubung ke server. Periksa koneksi internet Anda.",
                8000,
            );
        } finally {
            if (isGenerating) setLoading(false);
        }
    });

    if (!document.getElementById("spin-style")) {
        const s = document.createElement("style");
        s.id = "spin-style";
        s.textContent = `@keyframes spin{to{transform:rotate(360deg)}}`;
        document.head.appendChild(s);
    }

    async function loadSessionData() {
        const emptyEl = document.getElementById("empty_history");

        if (!token) {
            console.warn("[AI Studio] Token tidak ada, skip loadSessionData");
            if (emptyEl)
                emptyEl.textContent = "Silakan login untuk melihat riwayat.";
            return;
        }
        if (!window.aiSessionUrl) {
            console.warn("[AI Studio] aiSessionUrl tidak terdefinisi");
            return;
        }

        try {
            const res = await fetch(window.aiSessionUrl, {
                headers: {
                    Accept: "application/json",
                    Authorization: "Bearer " + token,
                    "X-CSRF-TOKEN": window.csrfToken,
                },
            });

            console.log("[AI Studio] Session response status:", res.status);

            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                console.error(
                    "[AI Studio] Session error:",
                    res.status,
                    errData,
                );
                if (emptyEl) emptyEl.textContent = "Gagal memuat riwayat.";
                return;
            }

            const data = await res.json();
            console.log("[AI Studio] Session data:", data);

            if (typeof data.credits_left !== "undefined") {
                updateCredits(data.credits_left);
            }

            if (data.histories && data.histories.length > 0) {
                const existingEmpty = document.getElementById("empty_history");
                existingEmpty?.remove();

                [...data.histories].reverse().forEach((h) => {
                    addToHistory(h.image_url, h.prompt, h.created_at || "");
                });
            } else {
                if (emptyEl)
                    emptyEl.textContent = "Belum ada desain yang di-generate.";
            }
        } catch (e) {
            console.error("[AI Studio] loadSessionData exception:", e);
            const emptyEl2 = document.getElementById("empty_history");
            if (emptyEl2) emptyEl2.textContent = "Gagal memuat riwayat.";
        }
    }

    loadSessionData();
});

window.showHistoryImage = function (url) {
    const wrapper = document.getElementById("ai_image_wrapper");
    const placeholder = document.getElementById("ai_result_placeholder");
    const img = document.getElementById("ai_generated_image");
    if (!img) return;
    img.src = url;
    wrapper.classList.remove("hidden");
    placeholder.classList.add("hidden");
};

function downloadImg() {
    const imgEl = document.getElementById("ai_generated_image");
    if (!imgEl?.src) return;

    const link = document.createElement("a");
    link.href = imgEl.src;
    link.download = "roupaa-ai-design-" + Date.now() + ".png";
    link.click();
}

function gunakanDesain() {
    const imgEl = document.getElementById("ai_generated_image");
    if (!imgEl?.src) return;

    sessionStorage.setItem("ai_design_url", imgEl.src);

    window.location.href = window.aiOrderUrl || "/customer/custom-order";
}
