document.addEventListener("DOMContentLoaded", async function () {
    const token = sessionStorage.getItem("access_token");
    const loadingState = document.getElementById("loading-state");
    const emptyState = document.getElementById("empty-state");
    const galleryContainer = document.getElementById("gallery-container");

    try {
        const response = await fetch(window.aiHistoryUrl, {
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

        if (response.ok && result.data && result.data.data.length > 0) {
            galleryContainer.classList.remove("hidden");

            result.data.data.forEach((history) => {
                const dateObj = new Date(history.created_at);
                const formattedDate = dateObj.toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                });
                const imagePath = `/storage/${history.image_path}`;

                const cardHtml = `
                    <div class="bg-surface-container-lowest border border-outline-variant/20 rounded-2xl overflow-hidden shadow-lg flex flex-col">
                        <div onclick="openModal('${imagePath}')" class="block group relative overflow-hidden border-b border-white/10 shadow-md cursor-zoom-in">
                            <img src="${imagePath}" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                                <span class="material-symbols-outlined text-white text-4xl">zoom_in</span>
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow bg-surface-container-low">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[9px] text-primary font-bold uppercase tracking-widest">${formattedDate}</span>
                                <a href="${imagePath}" download="roupaa-design-${history.id}.png"
                                    class="text-on-surface-variant hover:text-white transition-colors" title="Download">
                                    <span class="material-symbols-outlined text-[18px]">download</span>
                                </a>
                            </div>
                            <p class="text-xs text-on-surface-variant line-clamp-3 leading-relaxed mb-4">"${history.prompt}"</p>
                        </div>
                    </div>
                `;
                galleryContainer.innerHTML += cardHtml;
            });
        } else {
            emptyState.classList.remove("hidden");
            emptyState.classList.add("flex");
        }
    } catch (error) {
        console.error("Error fetching data:", error);
        loadingState.innerHTML = `<span class="text-red-500 font-bold">Terjadi kesalahan saat memuat data.</span>`;
    }
});

function openModal(imageSrc) {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    modalImg.src = imageSrc;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    setTimeout(() => {
        modal.classList.remove("opacity-0");
        modalImg.classList.remove("scale-95");
        modalImg.classList.add("scale-100");
    }, 10);
    document.body.style.overflow = "hidden";
}

function closeModal() {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    modal.classList.add("opacity-0");
    modalImg.classList.remove("scale-100");
    modalImg.classList.add("scale-95");
    setTimeout(() => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.style.overflow = "auto";
    }, 300);
}

document.getElementById("imageModal").addEventListener("click", function (e) {
    if (e.target === this) closeModal();
});

document.addEventListener("keydown", function (e) {
    if (
        e.key === "Escape" &&
        !document.getElementById("imageModal").classList.contains("hidden")
    ) {
        closeModal();
    }
});
