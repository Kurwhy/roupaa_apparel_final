function toggleUserMenu() {
    const menu = document.getElementById("user-dropdown");
    menu.classList.toggle("opacity-0");
    menu.classList.toggle("invisible");
    menu.classList.toggle("translate-y-[-10px]");
}

async function handleLogout() {
    const token = sessionStorage.getItem("access_token");
    if (token) {
        try {
            await fetch("/logout", {
                method: "POST",
                headers: {
                    Authorization: "Bearer " + token,
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                },
            });
        } catch (error) {
            console.error("Gagal menghubungi server untuk logout:", error);
        }
    }
    sessionStorage.removeItem("access_token");
    sessionStorage.removeItem("user_role");
    sessionStorage.removeItem("user_email");
    window.location.href = "/";
}

document.addEventListener("DOMContentLoaded", () => {
    const token = sessionStorage.getItem("access_token");
    const guestBtn = document.getElementById("nav-guest-btn");
    const authMenu = document.getElementById("nav-auth-menu");

    if (token) {
        if (authMenu) authMenu.classList.remove("hidden");
        var notifWrapper = document.getElementById("customer-notif-wrapper");
        if (notifWrapper) notifWrapper.style.display = "block";

        const userName = sessionStorage.getItem("user_name");
        const userEmail = sessionStorage.getItem("user_email");

        if (userName) {
            const nameEl = document.getElementById("nav-user-name");
            if (nameEl) nameEl.textContent = userName;
            const dropdownName = document.getElementById("nav-dropdown-name");
            if (dropdownName) dropdownName.textContent = userName;
            const mobileName = document.getElementById("mobile-user-name");
            if (mobileName) mobileName.textContent = userName;
        }
        if (userEmail) {
            const emailEl = document.getElementById("nav-user-email");
            if (emailEl) emailEl.textContent = userEmail;
            const mobileEmail = document.getElementById("mobile-user-email");
            if (mobileEmail) mobileEmail.textContent = userEmail;
        }

        const mobileAuth = document.getElementById("mobile-auth-section");
        if (mobileAuth) mobileAuth.style.display = "block";

        if (typeof window.Echo.private !== "function") {
            window.Echo = new Echo({
                broadcaster: "reverb",
                key: document.querySelector('meta[name="reverb-app-key"]')
                    ?.content,
                wsHost: document.querySelector('meta[name="reverb-host"]')
                    ?.content,
                wsPort:
                    parseInt(
                        document.querySelector('meta[name="reverb-port"]')
                            ?.content,
                    ) || 8080,
                wssPort:
                    parseInt(
                        document.querySelector('meta[name="reverb-port"]')
                            ?.content,
                    ) || 8080,
                forceTLS: false,
                enabledTransports: ["ws"],
                authEndpoint: "/broadcasting/auth",
                auth: {
                    headers: {
                        Authorization: "Bearer " + token,
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        )?.content,
                    },
                },
            });
            window.dispatchEvent(new Event("echoReady"));
        }
    } else {
        if (guestBtn) guestBtn.classList.remove("hidden");
        const mobileGuest = document.getElementById("mobile-guest-btn");
        if (mobileGuest) mobileGuest.style.display = "flex";
    }

    document.addEventListener("click", function (event) {
        const container = document.getElementById("nav-auth-menu");
        const menu = document.getElementById("user-dropdown");
        if (
            container &&
            !container.contains(event.target) &&
            !menu.classList.contains("invisible")
        ) {
            toggleUserMenu();
        }
    });
});
