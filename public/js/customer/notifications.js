function escapeHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

(function initCustomerNotifications() {
    const TOKEN = sessionStorage.getItem("access_token");
    if (!TOKEN) return;

    function getJwtUserId(token) {
        try {
            const payload = JSON.parse(atob(token.split(".")[1]));
            return payload.sub || null;
        } catch (e) {
            return null;
        }
    }

    const userId = getJwtUserId(TOKEN);
    if (!userId) return;

    try {
        if (!window._notifAudioCtx) {
            window._notifAudioCtx = new (
                window.AudioContext || window.webkitAudioContext
            )();
        }
    } catch (e) {}

    document.addEventListener("click", function () {
        if (
            window._notifAudioCtx &&
            window._notifAudioCtx.state === "suspended"
        ) {
            window._notifAudioCtx.resume();
        }
    });

    function playNotifSound() {
        try {
            var ctx = window._notifAudioCtx;
            if (!ctx) return;

            function doPlayCustomer() {
                var osc1 = ctx.createOscillator();
                var gain1 = ctx.createGain();
                osc1.type = "sine";
                osc1.frequency.setValueAtTime(880, ctx.currentTime);
                osc1.frequency.setValueAtTime(1100, ctx.currentTime + 0.08);
                gain1.gain.setValueAtTime(0.3, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(
                    0.01,
                    ctx.currentTime + 0.4,
                );
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(ctx.currentTime);
                osc1.stop(ctx.currentTime + 0.4);

                var osc2 = ctx.createOscillator();
                var gain2 = ctx.createGain();
                osc2.type = "sine";
                osc2.frequency.setValueAtTime(1320, ctx.currentTime + 0.12);
                gain2.gain.setValueAtTime(0.15, ctx.currentTime + 0.12);
                gain2.gain.exponentialRampToValueAtTime(
                    0.01,
                    ctx.currentTime + 0.55,
                );
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(ctx.currentTime + 0.12);
                osc2.stop(ctx.currentTime + 0.55);
            }

            if (ctx.state === "suspended") {
                ctx.resume().then(doPlayCustomer);
            } else {
                doPlayCustomer();
            }
        } catch (e) {}
    }

    var colorMap = {
        blue: "#60a5fa",
        green: "#4ade80",
        yellow: "#facc15",
        red: "#f87171",
        purple: "#c084fc",
    };

    function showToast(notif) {
        var existing = document.getElementById("customer-notif-toast");
        if (existing) existing.remove();

        var color = colorMap[notif.color] || colorMap.blue;
        var toast = document.createElement("div");
        toast.id = "customer-notif-toast";
        toast.style.cssText =
            "display:flex;align-items:flex-start;gap:12px;padding:14px 16px;" +
            "background:#1a1a1a;border:1px solid rgba(255,255,255,0.08);" +
            "border-left:3px solid " +
            color +
            ";" +
            "border-radius:14px;max-width:340px;" +
            "position:fixed;top:80px;right:20px;z-index:9999;" +
            "cursor:" +
            (notif.order_id ? "pointer" : "default") +
            ";" +
            "box-shadow:0 12px 40px rgba(0,0,0,0.7);pointer-events:auto;";

        toast.innerHTML =
            '<span class="material-symbols-outlined" style="color:' +
            color +
            ';font-size:20px;flex-shrink:0;margin-top:2px">' +
            escapeHtml(notif.icon) +
            "</span>" +
            '<div style="flex:1;min-width:0">' +
            '<p style="font-size:12px;font-weight:700;color:#fff;margin:0">' +
            escapeHtml(notif.title) +
            "</p>" +
            '<p style="font-size:10px;color:#9ca3af;margin-top:4px">' +
            escapeHtml(notif.message) +
            "</p>" +
            "</div>" +
            '<span class="material-symbols-outlined" onclick="event.stopPropagation();this.parentElement.remove()" ' +
            'style="color:rgba(255,255,255,0.15);font-size:16px;cursor:pointer;flex-shrink:0;margin-top:2px">close</span>';

        if (notif.order_id) {
            toast.onclick = function () {
                window.location.href =
                    "/customer/progress-pesanan/" + notif.order_id;
            };
        }

        document.body.appendChild(toast);
        setTimeout(function () {
            toast.style.transition = "opacity 0.3s,transform 0.3s";
            toast.style.opacity = "0";
            toast.style.transform = "translateX(120px)";
            setTimeout(function () {
                if (toast.parentElement) toast.remove();
            }, 300);
        }, 6000);
    }

    function setBadge(count) {
        var badge = document.getElementById("customer-notif-badge");
        var countEl = document.getElementById("customer-notif-count");
        if (!badge) return;
        if (count > 0) {
            badge.style.display = "flex";
            if (countEl) countEl.textContent = count > 99 ? "99+" : count;
        } else {
            badge.style.display = "none";
        }
    }

    function incrementBadge() {
        var countEl = document.getElementById("customer-notif-count");
        var badge = document.getElementById("customer-notif-badge");
        if (!countEl || !badge) return;
        var current = parseInt(countEl.textContent) || 0;
        badge.style.display = "flex";
        countEl.textContent = current + 1 > 99 ? "99+" : current + 1;
    }

    function fetchNotifications() {
        fetch("/customer/api/notifications", {
            headers: {
                Authorization: "Bearer " + TOKEN,
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
            },
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (json) {
                if (json.status !== "success") return;
                renderList(json.data.notifications);
                setBadge(json.data.unread_count);
            })
            .catch(function () {});
    }

    function renderList(notifications) {
        var list = document.getElementById("customer-notif-list");
        if (!list) return;

        if (!notifications || notifications.length === 0) {
            list.innerHTML =
                '<div style="padding:32px;text-align:center">' +
                '<span class="material-symbols-outlined" style="color:rgba(255,255,255,0.1)">notifications_off</span>' +
                '<p style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:8px">Belum ada notifikasi</p></div>';
            return;
        }

        var colorRgbMap = {
            blue: "59,130,246",
            green: "34,197,94",
            yellow: "234,179,8",
            red: "239,68,68",
            purple: "168,85,247",
        };

        list.innerHTML = notifications
            .map(function (n) {
                var rgb = colorRgbMap[n.color] || colorRgbMap.blue;
                var dot = !n.is_read
                    ? '<span style="position:absolute;top:12px;right:12px;width:6px;height:6px;background:#f2ca50;border-radius:50%"></span>'
                    : "";
                var bg = !n.is_read ? "background:rgba(242,202,80,0.03);" : "";
                var click = n.order_id
                    ? 'onclick="goToCustomerNotif(' +
                      n.id +
                      "," +
                      n.order_id +
                      ')"'
                    : 'onclick="markCustomerNotifRead(' + n.id + ')"';

                return (
                    '<div style="position:relative;padding:12px 20px;border-bottom:1px solid rgba(255,255,255,0.03);cursor:pointer;' +
                    bg +
                    '" ' +
                    click +
                    ">" +
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
                    '<p style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:2px">' +
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

    function prependNotif(notif) {
        var list = document.getElementById("customer-notif-list");
        if (!list) return;
        var empty = list.querySelector('[style*="notifications_off"]');
        if (empty) list.innerHTML = "";

        var colorRgbMap = {
            blue: "59,130,246",
            green: "34,197,94",
            yellow: "234,179,8",
            red: "239,68,68",
            purple: "168,85,247",
        };
        var rgb = colorRgbMap[notif.color] || colorRgbMap.blue;
        var click = notif.order_id
            ? 'onclick="goToCustomerNotif(' +
              notif.id +
              "," +
              notif.order_id +
              ')"'
            : 'onclick="markCustomerNotifRead(' + notif.id + ')"';

        list.insertAdjacentHTML(
            "afterbegin",
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
                "</div></div></div>",
        );
    }

    window.toggleCustomerNotif = function () {
        var dd = document.getElementById("customer-notif-dropdown");
        if (!dd) return;
        var isHidden = dd.style.display === "none" || !dd.style.display;
        dd.style.display = isHidden ? "block" : "none";
        if (isHidden) {
            setTimeout(function () {
                document.addEventListener("click", closeOnOutside);
            }, 100);
        }
    };

    function closeOnOutside(e) {
        var dd = document.getElementById("customer-notif-dropdown");
        var btn = document.getElementById("customer-notif-btn");
        if (!dd || !btn) return;
        if (!dd.contains(e.target) && !btn.contains(e.target)) {
            dd.style.display = "none";
            document.removeEventListener("click", closeOnOutside);
        }
    }

    window.goToCustomerNotif = function (notifId, orderId) {
        markCustomerNotifRead(notifId);
        window.location.href = "/customer/progress-pesanan/" + orderId;
    };

    window.markCustomerNotifRead = function (notifId) {
        fetch("/customer/api/notifications/" + notifId + "/read", {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
                Accept: "application/json",
            },
        }).then(function () {
            var countEl = document.getElementById("customer-notif-count");
            var badge = document.getElementById("customer-notif-badge");
            if (!countEl || !badge) return;
            var next = Math.max(0, (parseInt(countEl.textContent) || 0) - 1);
            if (next === 0) badge.style.display = "none";
            else countEl.textContent = next;
        });
    };

    window.customerMarkAllRead = function () {
        fetch("/customer/api/notifications/read-all", {
            method: "POST",
            headers: {
                Authorization: "Bearer " + TOKEN,
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
                Accept: "application/json",
            },
        }).then(function () {
            setBadge(0);
            fetchNotifications();
        });
    };

    let subscribed = false;

    function subscribe() {
        if (subscribed) return;
        subscribed = true;

        window.Echo.private("customer.notifications." + userId).listen(
            ".NewCustomerNotification",
            function (e) {
                playNotifSound();
                showToast(e);
                prependNotif(e);
                incrementBadge();
            },
        );
    }

    function trySubscribe(attempt) {
        if (subscribed) return;
        if (window.Echo && typeof window.Echo.private === "function") {
            subscribe();
        } else if (attempt < 30) {
            setTimeout(function () {
                trySubscribe(attempt + 1);
            }, 200);
        }
    }

    fetchNotifications();
    trySubscribe(0);
})();
