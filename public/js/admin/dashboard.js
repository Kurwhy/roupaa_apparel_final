document.getElementById("current_date").textContent =
    new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });

function updateClock() {
    var el = document.getElementById("realtime-clock");
    if (el)
        el.textContent =
            new Date().toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            }) + " WIB";
}
setInterval(updateClock, 1000);
updateClock();

document.addEventListener("DOMContentLoaded", async function () {
    var userName = sessionStorage.getItem("user_name");
    var userEmail = sessionStorage.getItem("user_email");
    var userRole = sessionStorage.getItem("user_role");
    var greetingEl = document.getElementById("user_greeting");

    if (userName) {
        greetingEl.textContent = userName;
    } else if (userEmail) {
        var n = userEmail.split("@")[0];
        greetingEl.textContent = n.charAt(0).toUpperCase() + n.slice(1);
    } else {
        greetingEl.textContent = userRole === "owner" ? "Owner" : "Admin";
    }

    await loadDashboard();

    var checkEcho = setInterval(function () {
        if (typeof window.Echo !== "undefined") {
            clearInterval(checkEcho);
            console.info("[Dashboard] Listening for real-time updates...");

            window.Echo.private("admin.notifications").listen(
                ".notification.new",
                function (e) {
                    console.info(
                        "[Dashboard] Update received, refreshing data...",
                    );
                    loadDashboard();
                },
            );
        }
    }, 1000);
});

async function loadDashboard() {
    var token = sessionStorage.getItem("access_token");
    if (!token) {
        window.location.href = "/login";
        return;
    }

    try {
        var res = await fetch("/ops/api/dashboard", {
            headers: {
                Authorization: "Bearer " + token,
                Accept: "application/json",
            },
        });

        if (res.status === 401) {
            sessionStorage.clear();
            window.location.href = "/login";
            return;
        }

        var json = await res.json();
        if (json.status !== "success") return;

        var data = json.data;

        renderStats(data.stats);
        setText("stat_customers", data.total_customers || 0);
        setText("stat_unread_chats", data.unread_chats || 0);
        renderNewOrders(data.new_orders);
        renderOrdersTable(data.latest_orders);
        renderActionPanel(data.action_needed);
    } catch (error) {
        console.error("Dashboard fetch error:", error);
    }
}

function setText(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = value;
}

function renderStats(stats) {
    var diskusi =
        (stats.diskusi_desain || 0) +
        (stats.menunggu_spesifikasi || 0) +
        (stats.menunggu_estimasi || 0);

    setText("stat_diskusi", diskusi);
    setText("stat_menunggu_dp", stats.menunggu_pembayaran || 0);
    setText("stat_produksi", stats.diproses || 0);
    setText("stat_selesai", (stats.siap_diambil || 0) + (stats.selesai || 0));
}

function renderNewOrders(orders) {
    var section = document.getElementById("new_orders_section");
    var list = document.getElementById("new_orders_list");
    var countEl = document.getElementById("new_orders_count");
    if (!section || !list) return;

    if (!orders || orders.length === 0) {
        section.classList.add("hidden");
        return;
    }

    section.classList.remove("hidden");
    if (countEl) countEl.textContent = orders.length;

    list.innerHTML = orders
        .map(function (o) {
            var instansi = o.pelanggan.nama_instansi_brand
                ? ' · <span style="color:rgba(255,255,255,0.3)">' +
                o.pelanggan.nama_instansi_brand +
                "</span>"
                : "";

            return (
                '<a href="/ops/pesanan/' +
                o.id +
                '" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-0 px-6 py-4 hover:bg-blue-500/5 transition-colors group">' +
                '<div class="flex items-center gap-4">' +
                '<div style="width:36px;height:36px;border-radius:10px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">' +
                '<span class="material-symbols-outlined" style="font-size:18px;color:#60a5fa">mail</span>' +
                "</div>" +
                "<div>" +
                '<p style="font-size:12px;font-weight:700;color:#fff;margin:0">' +
                o.pelanggan.nama_lengkap +
                instansi +
                "</p>" +
                '<p style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:2px">' +
                '<span style="color:#f2ca50;font-weight:700">#' +
                o.order_number +
                "</span> · " +
                o.project_name +
                "</p>" +
                "</div>" +
                "</div>" +
                '<div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-3">' +
                '<span style="font-size:10px;color:rgba(255,255,255,0.25)">' +
                o.created_at +
                "</span>" +
                '<span class="material-symbols-outlined group-hover:translate-x-1 transition-transform" style="font-size:16px;color:rgba(255,255,255,0.2)">arrow_forward</span>' +
                "</div>" +
                "</a>"
            );
        })
        .join("");
}

function renderOrdersTable(orders) {
    var tbody = document.getElementById("latest_orders_table");
    if (!tbody) return;

    if (!orders || orders.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="px-6 py-10 text-center text-on-surface-variant/50 text-xs">Belum ada pesanan.</td></tr>';
        return;
    }

    var statusColors = {
        diskusi_desain: {
            bg: "rgba(59,130,246,0.12)",
            text: "#93c5fd",
            border: "rgba(59,130,246,0.25)",
            label: "Diskusi",
        },
        menunggu_spesifikasi: {
            bg: "rgba(168,85,247,0.12)",
            text: "#c4b5fd",
            border: "rgba(168,85,247,0.25)",
            label: "Spesifikasi",
        },
        menunggu_estimasi: {
            bg: "rgba(249,115,22,0.12)",
            text: "#fdba74",
            border: "rgba(249,115,22,0.25)",
            label: "Estimasi",
        },
        menunggu_pembayaran: {
            bg: "rgba(234,179,8,0.12)",
            text: "#fde047",
            border: "rgba(234,179,8,0.25)",
            label: "Bayar",
        },
        diproses: {
            bg: "rgba(242,202,80,0.12)",
            text: "#f2ca50",
            border: "rgba(242,202,80,0.25)",
            label: "Produksi",
        },
        siap_diambil: {
            bg: "rgba(34,197,94,0.12)",
            text: "#86efac",
            border: "rgba(34,197,94,0.25)",
            label: "Siap",
        },
        selesai: {
            bg: "rgba(34,197,94,0.12)",
            text: "#86efac",
            border: "rgba(34,197,94,0.25)",
            label: "Selesai",
        },
        dibatalkan: {
            bg: "rgba(239,68,68,0.12)",
            text: "#fca5a5",
            border: "rgba(239,68,68,0.25)",
            label: "Batal",
        },
    };

    tbody.innerHTML = orders
        .map(function (order) {
            var s = statusColors[order.status] || {
                bg: "rgba(255,255,255,0.1)",
                text: "#fff",
                border: "rgba(255,255,255,0.2)",
                label: order.status,
            };

            var instansi = order.pelanggan.nama_instansi_brand
                ? '<span style="display:block;font-size:9px;color:rgba(255,255,255,0.3);margin-top:1px">' +
                order.pelanggan.nama_instansi_brand +
                "</span>"
                : "";

            var chatBadge = "";
            if (order.unread_chats > 0) {
                chatBadge =
                    '<span style="display:inline-flex;align-items:center;gap:3px;background:rgba(59,130,246,0.15);color:#60a5fa;border:1px solid rgba(59,130,246,0.25);font-size:9px;font-weight:700;padding:2px 6px;border-radius:6px;margin-left:6px" title="' +
                    order.unread_chats +
                    ' chat belum dibaca">' +
                    '<span class="material-symbols-outlined" style="font-size:11px">chat</span>' +
                    order.unread_chats +
                    "</span>";
            }

            return (
                '<tr class="hover:bg-white/[0.02] transition-colors">' +
                '<td class="px-6 py-3.5" style="font-size:11px;font-weight:700;color:#f2ca50;font-family:monospace">#' +
                order.order_number +
                "</td>" +
                '<td class="px-6 py-3.5"><span style="font-size:12px;color:#fff">' +
                order.pelanggan.nama_lengkap +
                "</span>" +
                instansi +
                "</td>" +
                '<td class="px-6 py-3.5"><span style="font-size:12px;color:rgba(255,255,255,0.5)">' +
                order.project_name +
                chatBadge +
                "</span></td>" +
                '<td class="px-6 py-3.5">' +
                '<span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;padding:3px 8px;border-radius:6px;background:' +
                s.bg +
                ";color:" +
                s.text +
                ";border:1px solid " +
                s.border +
                '">' +
                s.label +
                "</span>" +
                "</td>" +
                '<td class="px-6 py-3.5 text-right">' +
                '<a href="/ops/pesanan/' +
                order.id +
                "\" style=\"display:inline-flex;width:28px;height:28px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);align-items:center;justify-content:center;color:rgba(255,255,255,0.3);transition:all 0.2s\" onmouseover=\"this.style.background='#f2ca50';this.style.color='#000';this.style.borderColor='#f2ca50'\" onmouseout=\"this.style.background='rgba(255,255,255,0.04)';this.style.color='rgba(255,255,255,0.3)';this.style.borderColor='rgba(255,255,255,0.08)'\">" +
                '<span class="material-symbols-outlined" style="font-size:14px">arrow_forward</span>' +
                "</a>" +
                "</td>" +
                "</tr>"
            );
        })
        .join("");
}

function renderActionPanel(actions) {
    var panel = document.getElementById("notifications_panel");
    if (!panel) return;

    if (!actions || actions.length === 0) {
        panel.innerHTML =
            '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 0;opacity:0.4">' +
            '<span class="material-symbols-outlined" style="font-size:32px;color:#4ade80;margin-bottom:8px">check_circle</span>' +
            '<p style="font-size:12px;color:#fff;font-weight:700">Semua Terkendali</p>' +
            '<p style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:4px">Tidak ada tindakan yang diperlukan.</p>' +
            "</div>";
        return;
    }

    var colorStyles = {
        red: {
            icon: "#f87171",
            bg: "rgba(239,68,68,0.06)",
            border: "rgba(239,68,68,0.12)",
        },
        yellow: {
            icon: "#facc15",
            bg: "rgba(234,179,8,0.06)",
            border: "rgba(234,179,8,0.12)",
        },
        green: {
            icon: "#4ade80",
            bg: "rgba(34,197,94,0.06)",
            border: "rgba(34,197,94,0.12)",
        },
        blue: {
            icon: "#60a5fa",
            bg: "rgba(59,130,246,0.06)",
            border: "rgba(59,130,246,0.12)",
        },
    };

    panel.innerHTML = actions
        .map(function (a) {
            var c = colorStyles[a.color] || colorStyles.blue;

            var itemsHtml = "";
            if (a.items && a.items.length > 0) {
                itemsHtml =
                    '<div style="margin-top:10px;display:flex;flex-direction:column;gap:4px;max-height:120px;overflow-y:auto">' +
                    a.items
                        .map(function (item) {
                            var sc = item.stock <= 5 ? "#f87171" : "#facc15";
                            return (
                                '<div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border-radius:8px;background:rgba(0,0,0,0.15)">' +
                                '<span style="font-size:10px;color:rgba(255,255,255,0.5);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;margin-right:8px">' +
                                item.name +
                                "</span>" +
                                '<span style="font-size:10px;font-weight:700;color:' +
                                sc +
                                ';flex-shrink:0">' +
                                item.stock +
                                " " +
                                item.unit +
                                "</span>" +
                                "</div>"
                            );
                        })
                        .join("") +
                    "</div>";
            }

            return (
                '<div style="padding:14px;border-radius:12px;background:' +
                c.bg +
                ";border:1px solid " +
                c.border +
                '">' +
                '<div style="display:flex;gap:10px;align-items:flex-start">' +
                '<span class="material-symbols-outlined" style="font-size:20px;color:' +
                c.icon +
                ';flex-shrink:0;margin-top:1px">' +
                a.icon +
                "</span>" +
                "<div>" +
                '<p style="font-size:12px;font-weight:700;color:#fff;margin:0">' +
                a.title +
                "</p>" +
                '<p style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:3px;line-height:1.4">' +
                a.message +
                "</p>" +
                "</div>" +
                "</div>" +
                itemsHtml +
                "</div>"
            );
        })
        .join("");
}
