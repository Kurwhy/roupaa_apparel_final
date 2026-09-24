document.addEventListener("DOMContentLoaded", async function () {
    const token = sessionStorage.getItem("access_token");
    const loadingState = document.getElementById("loading-state");
    const emptyState = document.getElementById("empty-state");
    const ordersContainer = document.getElementById("orders-container");

    try {
        const response = await fetch(window.orderApiUrl, {
            method: "GET",
            headers: {
                Authorization: "Bearer " + token,
                Accept: "application/json",
            },
        });

        if (response.status === 401) {
            sessionStorage.removeItem("access_token");
            sessionStorage.removeItem("user_role");
            sessionStorage.removeItem("user_email");
            window.location.href = "/login";
            return;
        }

        const result = await response.json();
        loadingState.classList.add("hidden");

        if (response.ok && result.data.length > 0) {
            ordersContainer.classList.remove("hidden");

            result.data.forEach((order) => {
                const dateObj = new Date(order.created_at);
                const formattedDate = dateObj.toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                });
                const icon = order.product?.icon || "inventory_2";
                const productName = order.product?.name || "Produk Custom";

                const statusLabels = {
                    diskusi_desain: { label: "Fase Desain", icon: "design_services", color: "blue" },
                    menunggu_spesifikasi: { label: "Isi Spesifikasi", icon: "assignment", color: "primary" },
                    menunggu_estimasi: { label: "Menunggu Estimasi", icon: "calculate", color: "primary" },
                    menunggu_pembayaran: { label: "Menunggu Pembayaran", icon: "payments", color: "yellow" },
                    diproses: { label: "Proses Produksi", icon: "precision_manufacturing", color: "blue" },
                    siap_diambil: { label: "Siap Diambil/Kirim", icon: "inventory_2", color: "green" },
                    selesai: { label: "Selesai", icon: "check_circle", color: "green" },
                    dibatalkan: { label: "Dibatalkan", icon: "cancel", color: "red" },
                };

                const colorClasses = {
                    blue: "text-blue-400 bg-blue-400/10 border-blue-400/30",
                    primary: "text-primary bg-primary/10 border-primary/30",
                    yellow: "text-yellow-400 bg-yellow-400/10 border-yellow-400/30",
                    green: "text-green-400 bg-green-400/10 border-green-400/30",
                    red: "text-red-400 bg-red-400/10 border-red-400/30",
                };

                const statusInfo = statusLabels[order.status] || {
                    label: order.status.replace(/_/g, " "),
                    icon: "info",
                    color: "blue",
                };

                let statusBadge = `
                <span class="inline-flex items-center gap-1.5 text-xs font-bold ${colorClasses[statusInfo.color]} border px-3 py-1.5 rounded uppercase tracking-widest">
                <span class="material-symbols-outlined text-[14px]">${statusInfo.icon}</span> ${statusInfo.label}
                </span>`;

                const orderHtml = `
                    <a href="${window.orderDetailBaseUrl}/${order.id}" class="block group">
                        <div class="bg-surface-container-low border border-outline-variant/30 group-hover:border-primary p-6 rounded transition-all shadow-md group-hover:shadow-primary/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div class="flex items-center gap-5 w-full md:w-auto">
                                <div class="w-14 h-14 bg-surface-container-high rounded flex items-center justify-center border border-outline-variant/50 flex-shrink-0">
                                    <span class="material-symbols-outlined text-3xl text-primary opacity-80">${icon}</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="text-xs font-bold text-primary tracking-widest uppercase">#${order.order_number}</span>
                                        <span class="text-[10px] text-on-surface-variant bg-background px-2 py-0.5 rounded border border-outline-variant/30">${formattedDate}</span>
                                    </div>
                                    <h3 class="font-headline font-black text-lg uppercase tracking-tight text-white group-hover:text-primary transition-colors">
                                        ${order.project_name}
                                    </h3>
                                    <p class="text-xs text-on-surface-variant mt-1">${productName}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between w-full md:w-auto gap-6 md:border-l md:border-outline-variant/20 md:pl-6">
                                <div class="text-left md:text-right">
                                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mb-1">Status Project</p>
                                    ${statusBadge}
                                </div>
                                <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-colors transform group-hover:translate-x-1">chevron_right</span>
                            </div>
                        </div>
                    </a>
                `;
                ordersContainer.innerHTML += orderHtml;
            });
        } else {
            emptyState.classList.remove("hidden");
            emptyState.classList.add("flex");
        }
    } catch (error) {
        console.error("Gagal mengambil data:", error);
        loadingState.innerHTML = `<span class="text-red-500 font-bold">Terjadi kesalahan pada server.</span>`;
    }
});
