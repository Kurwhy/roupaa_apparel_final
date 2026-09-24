var CONFIG = window.ADMIN_PROFILE_CONFIG;
var TOKEN = CONFIG.token;

var state = {
    nama_lengkap: "",
    email: "",
    no_telepon: "",
    nip_karyawan: "",
    divisi: "",
    role: "",
    member_since: "",
};

function loadProfile() {
    fetch(CONFIG.apiDataUrl, {
        headers: {
            Authorization: "Bearer " + TOKEN,
            Accept: "application/json",
        },
    })
        .then(function (r) {
            return r.json();
        })
        .then(function (d) {
            state.nama_lengkap = d.nama_lengkap || "";
            state.email = d.email || "";
            state.no_telepon = d.no_telepon || "";
            state.nip_karyawan = d.nip_karyawan || "";
            state.divisi = d.divisi || "";
            state.role = d.role || "";
            state.member_since = d.member_since || "";
            renderProfile();
        })
        .catch(function () {
            showToast("Gagal memuat data profil.", "red");
        });
}

function renderProfile() {
    var avatar = document.getElementById("avatar_circle");
    if (avatar && state.nama_lengkap) {
        var words = state.nama_lengkap.trim().split(/\s+/);
        avatar.textContent =
            words.length >= 2
                ? (words[0][0] + words[1][0]).toUpperCase()
                : words[0].substring(0, 2).toUpperCase();
    }

    removeSkeleton("header_name", state.nama_lengkap || "-");

    var roleBadge = document.getElementById("header_role_badge");
    if (roleBadge) {
        roleBadge.style.display = "inline-flex";
        if (state.role === "owner") {
            roleBadge.style.cssText +=
                ";background:rgba(168,85,247,0.12);border:1px solid rgba(168,85,247,0.3);color:#c084fc";
            roleBadge.textContent = "Owner";
        } else {
            roleBadge.style.cssText +=
                ";background:rgba(242,202,80,0.1);border:1px solid rgba(242,202,80,0.25);color:#f2ca50";
            roleBadge.textContent = "Admin";
        }
    }

    setText(
        "header_nip",
        state.nip_karyawan ? "NIP: " + state.nip_karyawan : "",
    );
    setText("header_divisi", state.divisi ? state.divisi : "");
    setText(
        "header_since",
        state.member_since ? "Bergabung " + state.member_since : "",
    );

    removeSkeleton("display_nama", state.nama_lengkap || "-");
    removeSkeleton("display_email", state.email || "-");
    removeSkeleton("display_telepon", state.no_telepon || "-");
    removeSkeleton("display_nip", state.nip_karyawan || "-");

    setVal("input_nama", state.nama_lengkap);
    setVal("input_email", state.email);
    setVal("input_telepon", state.no_telepon);

    var telInput = document.getElementById("input_telepon");
    if (telInput) telInput.dispatchEvent(new Event('input'));
}

function removeSkeleton(id, text) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.remove("skeleton");
    el.style.height = "";
    el.style.minWidth = "";
    el.textContent = text;
}

function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
}

function setVal(id, val) {
    var el = document.getElementById(id);
    if (el) el.value = val || "";
}

var editingField = null;

var fieldMap = {
    nama: { display: "display_nama", input: "input_nama", actions: "actions_nama", err: "err_nama" },
    email: { display: "display_email", input: "input_email", actions: "actions_email", err: "err_email" },
    telepon: {
        display: "display_telepon",
        input: "wrap_input_telepon",
        actions: "actions_telepon",
        err: "err_telepon",
    },
};

function toggleEdit(field) {
    if (editingField && editingField !== field) cancelEdit(editingField);

    var m = fieldMap[field];
    var display = document.getElementById(m.display);
    var input = document.getElementById(m.input);
    var actions = document.getElementById(m.actions);

    editingField = field;
    display.style.display = "none";
    input.style.display = "block";
    actions.style.display = "flex";

    if (field === "email") {
        document.getElementById("email_warning").style.display = "flex";
    }

    clearErr(m.err);
    var actualInput = document.getElementById("input_" + field);
    if (actualInput) {
        actualInput.focus();
        if (field === "telepon") actualInput.dispatchEvent(new Event('input'));
    }
}

function cancelEdit(field) {
    var m = fieldMap[field];
    document.getElementById(m.display).style.display = "block";
    document.getElementById(m.input).style.display = "none";
    document.getElementById(m.actions).style.display = "none";
    clearErr(m.err);

    if (field === "email") {
        document.getElementById("email_warning").style.display = "none";
    }

    if (field === "nama") setVal("input_nama", state.nama_lengkap);
    if (field === "email") setVal("input_email", state.email);
    if (field === "telepon") {
        setVal("input_telepon", state.no_telepon);
        document.getElementById("input_telepon").dispatchEvent(new Event('input'));
    }

    editingField = null;
}

async function saveField(field) {
    var m = fieldMap[field];

    clearErr(m.err);

    var nama = document.getElementById("input_nama").value.trim();
    var email = document.getElementById("input_email").value.trim();
    var telepon = document.getElementById("input_telepon").value.trim();

    if (field === "nama") {
        if (!nama || nama.length < 3) {
            showErr(m.err, "Nama minimal 3 karakter.");
            return;
        }
        if (!/^[a-zA-Z\s]+$/.test(nama)) {
            showErr(m.err, "Nama hanya boleh huruf dan spasi.");
            return;
        }
    }
    if (field === "email") {
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showErr(m.err, "Format email tidak valid.");
            return;
        }
    }
    if (field === "telepon") {
        if (!telepon || !/^\d{10,13}$/.test(telepon)) {
            showErr(m.err, "Nomor telepon harus 10-13 digit angka.");
            return;
        }
    }

    var payload = {
        nama_lengkap: field === "nama" ? nama : state.nama_lengkap,
        email: field === "email" ? email : state.email,
        no_telepon: field === "telepon" ? telepon : state.no_telepon,
    };

    var btn = document.querySelector("#actions_" + field + " .save-btn");
    if (btn) {
        btn.disabled = true;
        btn.textContent = "Menyimpan...";
    }

    try {
        var res = await fetch(CONFIG.updateUrl, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": CONFIG.csrfToken,
            },
            body: JSON.stringify(payload),
        });
        var data = await res.json();

        if (res.ok) {
            if (field === "nama") state.nama_lengkap = nama;
            if (field === "email") state.email = email;
            if (field === "telepon") state.no_telepon = telepon;

            cancelEdit(field);
            renderProfile();
            showToast(data.message, "green");
        } else if (res.status === 422 && data.errors) {
            var map = {
                nama: "nama_lengkap",
                email: "email",
                telepon: "no_telepon",
            };
            var errKey = map[field];
            if (data.errors[errKey]) showErr(m.err, data.errors[errKey][0]);
        } else {
            showToast(data.message || "Terjadi kesalahan.", "red");
        }
    } catch (e) {
        showToast("Gagal terhubung ke server.", "red");
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = "Simpan";
        }
    }
}

function togglePw(id) {
    var input = document.getElementById(id);
    var icon = input.nextElementSibling.querySelector(
        ".material-symbols-outlined",
    );
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "visibility_off";
    } else {
        input.type = "password";
        icon.textContent = "visibility";
    }
}

document.addEventListener("DOMContentLoaded", function () {
    var pwNew = document.getElementById("pw_new");
    if (!pwNew) return;
    pwNew.addEventListener("input", function () {
        var pw = this.value;
        var strength = document.getElementById("pw_strength");
        var bar = document.getElementById("pw_strength_bar");
        var label = document.getElementById("pw_strength_label");
        if (!pw) {
            strength.style.display = "none";
            return;
        }

        strength.style.display = "block";
        var score = 0;
        if (pw.length >= 8) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (pw.length >= 12) score++;

        var pct = (score / 4) * 100;
        var color =
            score <= 1
                ? "#ef4444"
                : score === 2
                    ? "#f59e0b"
                    : score === 3
                        ? "#84cc16"
                        : "#22c55e";
        var labels = ["", "Lemah", "Cukup", "Kuat", "Sangat Kuat"];

        bar.style.width = pct + "%";
        bar.style.background = color;
        label.style.color = color;
        label.textContent = labels[score] || "";
    });
});

async function savePassword() {
    clearErr("err_pw_current");
    clearErr("err_pw_new");
    clearErr("err_pw_confirm");

    var current = document.getElementById("pw_current").value;
    var newPw = document.getElementById("pw_new").value;
    var confirm = document.getElementById("pw_confirm").value;

    if (!current) {
        showErr("err_pw_current", "Password saat ini wajib diisi.");
        return;
    }
    if (!newPw || newPw.length < 8) {
        showErr("err_pw_new", "Password minimal 8 karakter.");
        return;
    }
    if (!/[A-Z]/.test(newPw)) {
        showErr(
            "err_pw_new",
            "Password harus mengandung minimal satu huruf besar.",
        );
        return;
    }
    if (!/[0-9]/.test(newPw)) {
        showErr("err_pw_new", "Password harus mengandung minimal satu angka.");
        return;
    }
    if (newPw !== confirm) {
        showErr("err_pw_confirm", "Konfirmasi password tidak cocok.");
        return;
    }

    var btn = document.getElementById("btn_save_pw");
    btn.disabled = true;
    btn.textContent = "Menyimpan...";

    try {
        var res = await fetch(CONFIG.updatePasswordUrl, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": CONFIG.csrfToken,
            },
            body: JSON.stringify({
                current_password: current,
                password: newPw,
                password_confirmation: confirm,
            }),
        });
        var data = await res.json();

        if (res.ok) {
            document.getElementById("pw_current").value = "";
            document.getElementById("pw_new").value = "";
            document.getElementById("pw_confirm").value = "";
            document.getElementById("pw_strength").style.display = "none";
            showToast(data.message, "green");
        } else if (res.status === 422 && data.errors) {
            if (data.errors.current_password)
                showErr("err_pw_current", data.errors.current_password[0]);
            if (data.errors.password)
                showErr("err_pw_new", data.errors.password[0]);
        } else {
            showToast(data.message || "Terjadi kesalahan.", "red");
        }
    } catch (e) {
        showToast("Gagal terhubung ke server.", "red");
    } finally {
        btn.disabled = false;
        btn.textContent = "Ubah Password";
    }
}

function showErr(id, msg) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = "block";
}

function clearErr(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = "";
    el.style.display = "none";
}

function showToast(msg, type) {
    var el = document.createElement("div");
    el.className = "toast-profile";
    el.style.cssText +=
        type === "green"
            ? "background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.25);color:#4ade80"
            : "background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#f87171";
    el.innerHTML =
        '<span class="material-symbols-outlined" style="font-size:16px">' +
        (type === "green" ? "check_circle" : "error") +
        "</span>" +
        msg;
    document.body.appendChild(el);
    setTimeout(function () {
        el.style.transition = "opacity 0.3s";
        el.style.opacity = "0";
        setTimeout(function () {
            el.remove();
        }, 300);
    }, 3500);
}

document.addEventListener("DOMContentLoaded", loadProfile);
