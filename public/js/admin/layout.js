function debounce(fn, delay) {
    var timer;
    return function () {
        var self = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(self, args); }, delay || 400);
    };
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

document.addEventListener("DOMContentLoaded", function () {
    var token = sessionStorage.getItem("access_token");
    var role = sessionStorage.getItem("user_role");
    var name = sessionStorage.getItem("user_name") || "Admin";

    var nameEl = document.getElementById("sidebar_user_name");
    var roleEl = document.getElementById("sidebar_user_role");
    if (nameEl) nameEl.textContent = name;
    if (roleEl) roleEl.textContent = role === "owner" ? "Owner" : "Admin";

    if (role === "owner") {
        var ownerMenu = document.getElementById("owner_menu_area");
        if (ownerMenu) ownerMenu.classList.remove("hidden");
    }

    if (token) {
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    }

    var checkEcho = setInterval(function () {
        if (typeof window.Echo !== "undefined" && token) {
            clearInterval(checkEcho);
            console.info("[Notif] Channel admin.notifications connected");

            window.Echo.private("admin.notifications").listen(
                ".notification.new",
                function (e) {
                    console.info("[Notif] New:", e.notification);
                    prependNotification(e.notification);
                    updateBadgeCount(1);
                    playNotifSound();
                    showToast(e.notification);
                },
            );
        }
    }, 1000);
});

try {
    window._notifAudioCtx = new (
        window.AudioContext || window.webkitAudioContext
    )();
} catch (e) {}

document.addEventListener("click", function () {
    if (window._notifAudioCtx && window._notifAudioCtx.state === "suspended") {
        window._notifAudioCtx.resume();
    }
});

function playNotifSound() {
    try {
        var ctx = window._notifAudioCtx;
        if (!ctx) return;
        if (ctx.state === "suspended") {
            ctx.resume().then(function () {
                doPlay(ctx);
            });
        } else {
            doPlay(ctx);
        }
    } catch (e) {}
}

function doPlay(ctx) {
    var osc1 = ctx.createOscillator();
    var gain1 = ctx.createGain();
    osc1.type = "sine";
    osc1.frequency.setValueAtTime(880, ctx.currentTime);
    osc1.frequency.setValueAtTime(1100, ctx.currentTime + 0.08);
    gain1.gain.setValueAtTime(0.3, ctx.currentTime);
    gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(ctx.currentTime);
    osc1.stop(ctx.currentTime + 0.4);

    var osc2 = ctx.createOscillator();
    var gain2 = ctx.createGain();
    osc2.type = "sine";
    osc2.frequency.setValueAtTime(1320, ctx.currentTime + 0.12);
    gain2.gain.setValueAtTime(0.15, ctx.currentTime + 0.12);
    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.55);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(ctx.currentTime + 0.12);
    osc2.stop(ctx.currentTime + 0.55);
}

function showToast(notif) {
    var container = document.getElementById("toast_container");
    if (!container) {
        console.warn("[Toast] Container tidak ditemukan");
        return;
    }

    var colorMap = {
        blue: "#60a5fa",
        green: "#4ade80",
        yellow: "#facc15",
        red: "#f87171",
        purple: "#c084fc",
    };
    var color = colorMap[notif.color] || colorMap.blue;

    var toast = document.createElement("div");
    toast.className = "notif-toast-in";
    toast.style.cssText =
        "display:flex;align-items:flex-start;gap:12px;padding:14px 16px;" +
        "background:#1a1a1a;border:1px solid rgba(255,255,255,0.08);" +
        "border-left:3px solid " +
        color +
        ";" +
        "border-radius:14px;max-width:340px;cursor:pointer;" +
        "box-shadow:0 12px 40px rgba(0,0,0,0.7);pointer-events:auto;";

    if (notif.order_id) {
        toast.onclick = function () {
            window.location.href = "/ops/pesanan/" + notif.order_id;
        };
    }

    toast.innerHTML =
        '<span class="material-symbols-outlined" style="color:' +
        color +
        ';font-size:20px;flex-shrink:0;margin-top:2px">' +
        escapeHtml(notif.icon) +
        "</span>" +
        '<div style="flex:1;min-width:0">' +
        '<p style="font-size:12px;font-weight:700;color:#fff;margin:0;line-height:1.3">' +
        escapeHtml(notif.title) +
        "</p>" +
        '<p style="font-size:10px;color:#9ca3af;margin-top:4px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">' +
        escapeHtml(notif.message) +
        "</p>" +
        "</div>" +
        '<span class="material-symbols-outlined" onclick="event.stopPropagation();this.parentElement.remove()" ' +
        'style="color:rgba(255,255,255,0.15);font-size:16px;cursor:pointer;flex-shrink:0;margin-top:2px">close</span>';

    container.appendChild(toast);

    setTimeout(function () {
        toast.style.transition = "opacity 0.3s, transform 0.3s";
        toast.style.opacity = "0";
        toast.style.transform = "translateX(120px)";
        setTimeout(function () {
            if (toast.parentElement) toast.remove();
        }, 300);
    }, 6000);
}

function fetchNotifications() {
    var token = sessionStorage.getItem("access_token");
    fetch("/ops/api/notifications", {
        headers: {
            Authorization: "Bearer " + token,
            Accept: "application/json",
        },
    })
        .then(function (res) {
            return res.json();
        })
        .then(function (json) {
            if (json.status !== "success") return;
            renderNotificationList(json.data.notifications);
            setBadgeCount(json.data.unread_count);
        })
        .catch(function () {});
}

function renderNotificationList(notifications) {
    var list = document.getElementById("notif_list");
    if (!list) return;

    if (!notifications || notifications.length === 0) {
        list.innerHTML =
            '<div class="px-5 py-10 text-center">' +
            '<span class="material-symbols-outlined text-2xl" style="color:rgba(255,255,255,0.08)">notifications_off</span>' +
            '<p style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:8px">Belum ada notifikasi</p>' +
            "</div>";
        return;
    }

    var colorMap = {
        blue: "59,130,246",
        green: "34,197,94",
        yellow: "234,179,8",
        red: "239,68,68",
        purple: "168,85,247",
    };

    list.innerHTML = notifications
        .map(function (n) {
            var rgb = colorMap[n.color] || colorMap.blue;
            var unreadBg = !n.is_read
                ? "background:rgba(242,202,80,0.03);"
                : "";
            var dot = !n.is_read
                ? '<span style="position:absolute;top:12px;right:12px;width:6px;height:6px;background:#f2ca50;border-radius:50%"></span>'
                : "";
            var click = n.order_id
                ? 'onclick="goToNotifOrder(' + n.id + "," + n.order_id + ')"'
                : 'onclick="markNotifRead(' + n.id + ')"';

            return (
                '<div style="position:relative;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,0.03);cursor:pointer;' +
                unreadBg +
                '" ' +
                click +
                " onmouseover=\"this.style.background='rgba(255,255,255,0.03)'\" onmouseout=\"this.style.background='" +
                (!n.is_read ? "rgba(242,202,80,0.03)" : "") +
                "'\">" +
                dot +
                '<div style="display:flex;gap:10px;align-items:flex-start">' +
                '<div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(' +
                rgb +
                ',0.1)">' +
                '<span class="material-symbols-outlined" style="font-size:16px;color:rgba(' +
                rgb +
                ',0.8)">' +
                escapeHtml(n.icon) +
                "</span></div>" +
                '<div style="flex:1;min-width:0">' +
                '<p style="font-size:11px;font-weight:700;color:#fff;margin:0">' +
                escapeHtml(n.title) +
                "</p>" +
                '<p style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:2px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">' +
                escapeHtml(n.message) +
                "</p>" +
                '<p style="font-size:9px;color:rgba(255,255,255,0.15);margin-top:4px">' +
                n.created_at +
                "</p>" +
                "</div></div></div>"
            );
        })
        .join("");
}

function prependNotification(notif) {
    var list = document.getElementById("notif_list");
    if (!list) return;

    var empty = list.querySelector(".text-center");
    if (empty) list.innerHTML = "";

    var colorMap = {
        blue: "59,130,246",
        green: "34,197,94",
        yellow: "234,179,8",
        red: "239,68,68",
        purple: "168,85,247",
    };
    var rgb = colorMap[notif.color] || colorMap.blue;
    var click = notif.order_id
        ? 'onclick="goToNotifOrder(' + notif.id + "," + notif.order_id + ')"'
        : 'onclick="markNotifRead(' + notif.id + ')"';

    var html =
        '<div style="position:relative;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,0.03);cursor:pointer;background:rgba(242,202,80,0.05)" ' +
        click +
        ">" +
        '<span style="position:absolute;top:12px;right:12px;width:6px;height:6px;background:#f2ca50;border-radius:50%"></span>' +
        '<div style="display:flex;gap:10px;align-items:flex-start">' +
        '<div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(' +
        rgb +
        ',0.1)">' +
        '<span class="material-symbols-outlined" style="font-size:16px;color:rgba(' +
        rgb +
        ',0.8)">' +
        escapeHtml(notif.icon) +
        "</span></div>" +
        '<div style="flex:1">' +
        '<p style="font-size:11px;font-weight:700;color:#fff;margin:0">' +
        escapeHtml(notif.title) +
        "</p>" +
        '<p style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:2px">' +
        escapeHtml(notif.message) +
        "</p>" +
        '<p style="font-size:9px;color:rgba(255,255,255,0.15);margin-top:4px">Baru saja</p>' +
        "</div></div></div>";

    list.insertAdjacentHTML("afterbegin", html);
}

function setBadgeCount(count) {
    var badge = document.getElementById("notif_badge");
    var countEl = document.getElementById("notif_badge_count");
    if (!badge) return;

    if (count > 0) {
        badge.classList.remove("hidden");
        if (countEl) countEl.textContent = count > 99 ? "99+" : count;
    } else {
        badge.classList.add("hidden");
    }
}

function updateBadgeCount(delta) {
    var countEl = document.getElementById("notif_badge_count");
    var badge = document.getElementById("notif_badge");
    if (!countEl || !badge) return;

    var current = parseInt(countEl.textContent) || 0;
    var next = Math.max(0, current + delta);
    if (next > 0) {
        badge.classList.remove("hidden");
        countEl.textContent = next > 99 ? "99+" : next;
    } else {
        badge.classList.add("hidden");
    }
}


function goToNotifOrder(notifId, orderId) {
    markNotifRead(notifId);
    window.location.href = "/ops/pesanan/" + orderId;
}

function markNotifRead(notifId) {
    var token = sessionStorage.getItem("access_token");
    fetch("/ops/api/notifications/" + notifId + "/read", {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            Accept: "application/json",
        },
    }).then(function () {
        updateBadgeCount(-1);
    });
}

function markAllNotifRead() {
    var token = sessionStorage.getItem("access_token");
    fetch("/ops/api/notifications/read-all", {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            Accept: "application/json",
        },
    }).then(function () {
        setBadgeCount(0);
        fetchNotifications();
    });
}

function toggleNotifDropdown() {
    var dropdown = document.getElementById("notif_dropdown");
    if (!dropdown) return;
    dropdown.classList.toggle("hidden");

    if (!dropdown.classList.contains("hidden")) {
        setTimeout(function () {
            document.addEventListener("click", closeNotifOnOutside);
        }, 100);
    }
}

function closeNotifOnOutside(e) {
    var dropdown = document.getElementById("notif_dropdown");
    var btn = document.getElementById("notif_bell_btn");
    if (!dropdown || !btn) return;

    if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.add("hidden");
        document.removeEventListener("click", closeNotifOnOutside);
    }
}

function handleAdminLogout() {
    fetch("/logout", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            Authorization: "Bearer " + sessionStorage.getItem("access_token"),
            Accept: "application/json",
        },
    }).finally(function () {
        sessionStorage.clear();
        window.location.href = "/login";
    });
}

function toggleSidebarUserMenu() {
    var dd = document.getElementById("sidebar_user_dropdown");
    if (!dd) return;
    dd.classList.toggle("hidden");

    if (!dd.classList.contains("hidden")) {
        setTimeout(function () {
            document.addEventListener("click", closeSidebarUserMenu);
        }, 100);
    }
}

function closeSidebarUserMenu(e) {
    var dd = document.getElementById("sidebar_user_dropdown");
    var btn = dd?.previousElementSibling;
    if (!dd) return;
    if (!dd.contains(e.target) && !btn?.contains(e.target)) {
        dd.classList.add("hidden");
        document.removeEventListener("click", closeSidebarUserMenu);
    }
}
