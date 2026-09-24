document.addEventListener("DOMContentLoaded", () => {
    const storedToken = sessionStorage.getItem("access_token");
    const storedRole = sessionStorage.getItem("user_role");

    const currentPath = window.location.pathname;
    if (
        storedToken &&
        (currentPath === "/login" || currentPath === "/register")
    ) {
        window.location.href =
            storedRole === "pelanggan" ? "/" : "/ops/dashboard";
        return;
    }

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ?? "";

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPassword(password) {
        if (password.length < 8) return { valid: false, msg: "Password minimal 8 karakter." };
        if (!/[A-Z]/.test(password)) return { valid: false, msg: "Password harus mengandung minimal satu huruf besar." };
        if (!/[0-9]/.test(password)) return { valid: false, msg: "Password harus mengandung minimal satu angka." };
        return { valid: true };
    }

    function showError(elementId, message) {
        const el = document.getElementById(elementId);
        if (!el) return;
        el.innerText = message;
        el.classList.remove("hidden");
    }

    function resetErrors() {
        document.querySelectorAll("span[id^='error-']").forEach((el) => {
            el.classList.add("hidden");
            el.innerText = "";
        });
    }

    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const btnLogin = document.getElementById("btnLogin");
            const alertBox = document.getElementById("alert-error");
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;

            if (alertBox) alertBox.classList.add("hidden");

            if (!email) {
                if (alertBox) {
                    alertBox.innerText = "Email wajib diisi.";
                    alertBox.classList.remove("hidden");
                }
                return;
            }
            if (!isValidEmail(email)) {
                if (alertBox) {
                    alertBox.innerText = "Format email tidak valid.";
                    alertBox.classList.remove("hidden");
                }
                return;
            }
            if (!password) {
                if (alertBox) {
                    alertBox.innerText = "Password wajib diisi.";
                    alertBox.classList.remove("hidden");
                }
                return;
            }
            const pwCheck = isValidPassword(password);
            if (!pwCheck.valid) {
                if (alertBox) {
                    alertBox.innerText = pwCheck.msg;
                    alertBox.classList.remove("hidden");
                }
                return;
            }
            if (password.length < 8) {
                if (alertBox) {
                    alertBox.innerText = "Password minimal 8 karakter.";
                    alertBox.classList.remove("hidden");
                }
                return;
            }

            btnLogin.innerHTML = "Memproses...";
            btnLogin.disabled = true;

            try {
                const response = await fetch("/login", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({ email, password }),
                });

                const data = await response.json();

                if (response.ok) {
                    sessionStorage.setItem("access_token", data.access_token);
                    sessionStorage.setItem("user_role", data.user.role);
                    sessionStorage.setItem("user_email", data.user.email);
                    sessionStorage.setItem(
                        "user_name",
                        data.user.nama_lengkap ?? "",
                    );
                    window.location.href =
                        data.user.role === "pelanggan" ? "/" : "/ops/dashboard";
                } else {
                    if (alertBox) {
                        alertBox.innerText =
                            data.error || "Email atau kata sandi salah.";
                        alertBox.classList.remove("hidden");
                    }
                }
            } catch (error) {
                alert("Terjadi kesalahan pada koneksi server.");
            } finally {
                btnLogin.innerHTML = "Masuk";
                btnLogin.disabled = false;
            }
        });
    }

    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            resetErrors();

            const btnRegister = document.getElementById("btnRegister");
            const nama = document.getElementById("nama_lengkap").value.trim();
            const noWa = document.getElementById("no_whatsapp").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const konfirmasi =
                document.getElementById("password-confirm").value;

            let hasError = false;

            if (!nama) {
                showError("error-nama_lengkap", "Nama lengkap wajib diisi.");
                hasError = true;
            } else if (nama.length < 3) {
                showError("error-nama_lengkap", "Nama minimal 3 karakter.");
                hasError = true;
            } else if (!/^[a-zA-Z\s]+$/.test(nama)) {
                showError("error-nama_lengkap", "Nama hanya boleh huruf dan spasi.");
                hasError = true;
            }

            if (!noWa) {
                showError("error-no_whatsapp", "Nomor WhatsApp wajib diisi.");
                hasError = true;
            } else if (!/^\d+$/.test(noWa)) {
                showError("error-no_whatsapp", "Nomor WhatsApp hanya boleh berisi angka.");
                hasError = true;
            } else if (noWa.length < 10 || noWa.length > 13) {
                showError("error-no_whatsapp", "Nomor WhatsApp harus 10-13 digit angka.");
                hasError = true;
            }

            if (!email) {
                showError("error-email", "Email wajib diisi.");
                hasError = true;
            } else if (!isValidEmail(email)) {
                showError("error-email", "Format email tidak valid.");
                hasError = true;
            }

            const pwCheck = isValidPassword(password);
            if (!password) {
                showError("error-password", "Password wajib diisi.");
                hasError = true;
            } else if (!pwCheck.valid) {
                showError("error-password", pwCheck.msg);
                hasError = true;
            }

            if (password !== konfirmasi) {
                showError(
                    "error-password_confirmation",
                    "Konfirmasi password tidak cocok.",
                );
                hasError = true;
            }

            if (hasError) return;

            btnRegister.innerHTML = "Memproses...";
            btnRegister.disabled = true;

            try {
                const response = await fetch("/register", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        nama_lengkap: nama,
                        no_whatsapp: noWa,
                        email: email,
                        password: password,
                        password_confirmation: konfirmasi,
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    alert("Akun berhasil dibuat! Silakan login.");
                    window.location.href = "/login";
                } else if (response.status === 422) {
                    for (const field in data) {
                        showError(`error-${field}`, data[field][0]);
                    }
                }
            } catch (error) {
                alert("Gagal mendaftarkan akun.");
            } finally {
                btnRegister.innerHTML = "Daftar Akun";
                btnRegister.disabled = false;
            }
        });
    }
});

function closeToast() {
    const toast = document.getElementById("toast-success");
    if (toast) {
        toast.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        toast.style.opacity = "0";
        toast.style.transform = "translate(-50%, -20px)";
        setTimeout(() => toast.remove(), 500);
    }
}

const toast = document.getElementById("toast-success");
if (toast) setTimeout(() => closeToast(), 5000);
