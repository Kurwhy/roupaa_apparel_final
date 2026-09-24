document.addEventListener("DOMContentLoaded", () => {
    const CONFIG = window.PROFILE_CONFIG;
    const token = CONFIG.token;

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const role = sessionStorage.getItem("user_role");
    if (role && role !== "pelanggan") {
        window.location.href = "/ops/dashboard";
        return;
    }

    let state = {
        nama_lengkap: null,
        no_whatsapp: null,
        nama_instansi_brand: null,
        alamat_pengiriman: null,
        email: sessionStorage.getItem("user_email") || null,
        member_since: null,
    };

    async function loadProfileData() {
        try {
            const res = await fetch(CONFIG.apiDataUrl, {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${token}`,
                    "X-CSRF-TOKEN": CONFIG.csrfToken,
                },
            });

            if (res.status === 401) {
                sessionStorage.removeItem("access_token");
                window.location.href = "/login";
                return;
            }

            const data = await res.json();

            state.nama_lengkap = data.nama_lengkap || null;
            state.no_whatsapp = data.no_whatsapp || null;
            state.nama_instansi_brand = data.nama_instansi_brand || null;
            state.alamat_pengiriman = data.alamat_pengiriman || null;
            state.email = data.email || state.email;
            state.member_since = data.member_since || null;

            renderAll();
        } catch (err) {
            console.error("Gagal load profil:", err);
            renderAll();
        }
    }

    function empty(text) {
        return `<span class="inline-flex items-center gap-1 text-on-surface-variant/40 italic font-medium text-xs">
            <span class="material-symbols-outlined text-[14px]">info</span>${text}
        </span>`;
    }

    function renderAll() {
        const initEl = document.getElementById("avatar-initials");
        initEl.classList.remove("skeleton");
        initEl.style.width = initEl.style.height = "";
        if (state.nama_lengkap) {
            const w = state.nama_lengkap.trim().split(/\s+/);
            initEl.textContent = (
                w.length >= 2 ? w[0][0] + w[1][0] : w[0].substring(0, 2)
            ).toUpperCase();
        } else {
            initEl.textContent = "?";
        }

        const namaEl = document.getElementById("display-nama");
        namaEl.innerHTML = state.nama_lengkap
            ? `<span>${state.nama_lengkap}</span>`
            : empty("Belum diisi");

        const emailEl = document.getElementById("display-email");
        emailEl.innerHTML = state.email
            ? `<span>${state.email}</span>`
            : '<span class="text-on-surface-variant/40">—</span>';

        const memberEl = document.getElementById("display-member");
        memberEl.innerHTML = state.member_since
            ? `<span class="material-symbols-outlined text-[12px] text-primary">schedule</span> Member sejak ${state.member_since}`
            : "";

        const waEl = document.getElementById("display-wa");
        waEl.innerHTML = state.no_whatsapp
            ? `<span>${state.no_whatsapp}</span>`
            : empty("Belum diisi");

        const orgEl = document.getElementById("display-org");
        orgEl.innerHTML = state.nama_instansi_brand
            ? `<span>${state.nama_instansi_brand}</span>`
            : `<span class="text-on-surface-variant/40 font-medium">Perorangan</span>`;

        const alamatEl = document.getElementById("display-alamat");
        alamatEl.innerHTML = state.alamat_pengiriman
            ? `<span>${state.alamat_pengiriman}</span>`
            : empty("Belum diisi");

        renderProgress();
    }

    function renderProgress() {
        const tracked = ["nama_lengkap", "no_whatsapp", "alamat_pengiriman"];
        const filled = tracked.filter((k) => !!state[k]).length;
        const pct = Math.round((filled / tracked.length) * 100);

        const bar = document.getElementById("progress-bar");
        const lbl = document.getElementById("progress-pct");
        if (bar) bar.style.width = pct + "%";
        if (lbl) lbl.textContent = pct + "%";
    }

    loadProfileData();

    const modal = document.getElementById("edit-modal");
    const modalBox = document.getElementById("modal-box");
    const modalAlert = document.getElementById("modal-alert");
    const modalSaveBtn = document.getElementById("modal-save-btn");
    const modalBtnIcon = document.getElementById("modal-btn-icon");
    const modalBtnLbl = document.getElementById("modal-btn-label");

    const showModalStyles = () => {
        modal.classList.remove("opacity-0", "pointer-events-none");
        modalBox.classList.remove("scale-95");
        modalBox.classList.add("scale-100");
    };
    const hideModalStyles = () => {
        modal.classList.add("opacity-0", "pointer-events-none");
        modalBox.classList.remove("scale-100");
        modalBox.classList.add("scale-95");
    };

    window.openModal = function () {
        modalAlert.classList.add("hidden");
        modalAlert.innerHTML = "";
        setModalLoading(false);
        clearAllFieldErr();

        document.getElementById("f-nama").value = state.nama_lengkap || "";
        document.getElementById("f-wa").value = state.no_whatsapp || "";
        document.getElementById("f-email").value = state.email || "";
        document.getElementById("f-org").value = state.nama_instansi_brand || "";
        document.getElementById("f-alamat").value = state.alamat_pengiriman || "";

        modal.classList.remove("modal-hidden");
        showModalStyles();
        setTimeout(() => document.getElementById("f-nama").focus(), 150);
    };

    window.closeModal = function () {
        hideModalStyles();
        setTimeout(() => {
            modal.classList.add("modal-hidden");
        }, 300); 
    };

    window.handleModalBackdropClick = (e) => {
        if (e.target === modal) closeModal();
    };

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.classList.contains("opacity-0")) closeModal();
    });

    function setModalLoading(on) {
        modalSaveBtn.disabled = on;
        if (on) {
            modalSaveBtn.classList.add("opacity-70", "cursor-not-allowed");
        } else {
            modalSaveBtn.classList.remove("opacity-70", "cursor-not-allowed");
        }
        modalBtnIcon.textContent = on ? "hourglass_top" : "save";
        modalBtnLbl.textContent = on ? "Menyimpan..." : "Simpan Perubahan";
    }

    function showModalError(msg) {
        modalAlert.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;flex-shrink:0">error</span><span>${msg}</span>`;
        modalAlert.classList.remove("hidden");
    }

    function setFieldErr(id, msg) {
        const el = document.getElementById(`err-${id}`);
        const input = document.getElementById(id);
        if (!el || !input) return;

        el.textContent = msg || "";
        el.style.display = msg ? "block" : "none";

        if (msg) {
            input.classList.add("border-red-500", "focus:border-red-500", "focus:ring-red-500/20");
        } else {
            input.classList.remove("border-red-500", "focus:border-red-500", "focus:ring-red-500/20");
        }
    }

    function clearAllFieldErr() {
        ["f-nama", "f-wa", "f-email", "f-org", "f-alamat"].forEach((id) => setFieldErr(id, null));
    }

    window.saveModal = async function () {
        modalAlert.classList.add("hidden");
        clearAllFieldErr();

        const nama = document.getElementById("f-nama").value.trim();
        const wa = document.getElementById("f-wa").value.trim();
        const email = document.getElementById("f-email").value.trim();
        const org = document.getElementById("f-org").value.trim();
        const alamat = document.getElementById("f-alamat").value.trim();

        let hasErr = false;
        if (!nama || nama.length < 3) { setFieldErr("f-nama", "Nama minimal 3 karakter."); hasErr = true; }
        if (!/^[a-zA-Z\s]+$/.test(nama)) { setFieldErr("f-nama", "Nama hanya boleh huruf dan spasi."); hasErr = true; }
        if (!wa || !/^\d{10,13}$/.test(wa)) { setFieldErr("f-wa", "Nomor WhatsApp harus 10-13 digit angka."); hasErr = true; }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setFieldErr("f-email", "Format email tidak valid."); hasErr = true; }
        if (!alamat) { setFieldErr("f-alamat", "Alamat pengiriman wajib diisi."); hasErr = true; }

        if (hasErr) return;

        const payload = {
            nama_lengkap: nama,
            no_whatsapp: wa,
            email: email,
            nama_instansi_brand: org,
            alamat_pengiriman: alamat,
        };

        setModalLoading(true);

        try {
            const res = await fetch(CONFIG.updateUrl, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": CONFIG.csrfToken,
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (res.ok) {
                state.nama_lengkap = nama;
                state.no_whatsapp = wa;
                state.email = email;
                state.nama_instansi_brand = org || null;
                state.alamat_pengiriman = alamat;

                renderAll();
                closeModal();
            } else if (res.status === 422) {
                showModalError("Terdapat kesalahan pada data yang dimasukkan.");
                if (data.errors) {
                    const map = {
                        nama_lengkap: "f-nama",
                        no_whatsapp: "f-wa",
                        email: "f-email",
                        nama_instansi_brand: "f-org",
                        alamat_pengiriman: "f-alamat",
                    };
                    Object.entries(data.errors).forEach(([k, msgs]) => {
                        if (map[k]) setFieldErr(map[k], msgs[0]);
                    });
                }
            } else if (res.status === 401) {
                sessionStorage.clear();
                window.location.href = "/login";
            } else {
                showModalError(data.message || "Terjadi kesalahan. Coba lagi.");
            }
        } catch (err) {
            showModalError("Gagal terhubung ke server.");
        } finally {
            setModalLoading(false);
        }
    };

    function showPwErr(which, msg) {
        const el = document.getElementById(`err-pw-${which}`);
        const input = document.getElementById(`pw-${which}`);
        el.textContent = msg;
        el.style.display = "block";
        input.classList.add("border-red-500", "focus:border-red-500", "focus:ring-red-500/20");
    }

    function clearPwErr() {
        ["current", "new", "confirm"].forEach((w) => {
            const el = document.getElementById(`err-pw-${w}`);
            const input = document.getElementById(`pw-${w}`);
            el.textContent = "";
            el.style.display = "none";
            input.classList.remove("border-red-500", "focus:border-red-500", "focus:ring-red-500/20");
        });
    }

    document.getElementById("btn-save-pw").addEventListener("click", async function () {
        clearPwErr();

        const current = document.getElementById("pw-current").value;
        const newPw = document.getElementById("pw-new").value;
        const confirm = document.getElementById("pw-confirm").value;

        let hasErr = false;
        if (!current) { showPwErr("current", "Password saat ini wajib diisi."); hasErr = true; }
        if (!newPw || newPw.length < 8) { showPwErr("new", "Password minimal 8 karakter."); hasErr = true; }
        if (!/[A-Z]/.test(newPw)) { showPwErr("new", "Password harus mengandung minimal satu huruf besar."); hasErr = true; }
        if (!/[0-9]/.test(newPw)) { showPwErr("new", "Password harus mengandung minimal satu angka."); hasErr = true; }
        if (newPw !== confirm) { showPwErr("confirm", "Konfirmasi password tidak cocok."); hasErr = true; }

        if (hasErr) return;

        const btn = this;
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.classList.add("opacity-70", "cursor-not-allowed");
        btn.innerHTML = `<span class="material-symbols-outlined text-[18px] animate-spin">sync</span><span>Menyimpan...</span>`;

        try {
            const res = await fetch(CONFIG.updatePasswordUrl, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": CONFIG.csrfToken,
                    Authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({
                    current_password: current,
                    password: newPw,
                    password_confirmation: confirm,
                }),
            });

            const data = await res.json();

            if (res.ok) {
                document.getElementById("pw-current").value = "";
                document.getElementById("pw-new").value = "";
                document.getElementById("pw-confirm").value = "";
                btn.innerHTML = `<span class="material-symbols-outlined text-[18px]">check_circle</span><span>Tersimpan</span>`;
                btn.classList.replace("bg-primary", "bg-green-500");

                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace("bg-green-500", "bg-primary");
                }, 2500);
            } else if (res.status === 422 && data.errors) {
                if (data.errors.current_password) showPwErr("current", data.errors.current_password[0]);
                if (data.errors.password) showPwErr("new", data.errors.password[0]);
            } else if (res.status === 401) {
                sessionStorage.clear();
                window.location.href = "/login";
            } else {
                showPwErr("current", data.message || "Terjadi kesalahan. Coba lagi.");
            }
        } catch (err) {
            showPwErr("current", "Gagal terhubung ke server.");
        } finally {
            btn.disabled = false;
            btn.classList.remove("opacity-70", "cursor-not-allowed");
            if (btn.textContent.includes("Menyimpan")) btn.innerHTML = originalHtml;
        }
    });
});