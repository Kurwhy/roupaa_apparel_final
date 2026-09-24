document.addEventListener("DOMContentLoaded", function () {
    const reveals = document.querySelectorAll(".reveal");
    const revealOnScroll = new IntersectionObserver(
        function (entries) {
            entries.forEach((entry) => {
                if (entry.isIntersecting) entry.target.classList.add("active");
            });
        },
        { threshold: 0.15, rootMargin: "0px 0px -50px 0px" },
    );
    reveals.forEach((r) => revealOnScroll.observe(r));
});

document.addEventListener("DOMContentLoaded", () => {
    const token = sessionStorage.getItem("access_token");
    const role = sessionStorage.getItem("user_role");
    const guestBlock = document.getElementById("guest-block");
    const authBlock = document.getElementById("auth-block");
    const heroBtn = document.getElementById("hero-btn");
    const authBtn = document.getElementById("auth-btn");

    if (token) {
        if (authBlock) authBlock.classList.remove("js-hidden");
        const targetUrl =
            role === "pelanggan" ? "/customer/custom-order" : "/ops/dashboard";
        if (heroBtn) heroBtn.href = targetUrl;
        if (authBtn) authBtn.href = targetUrl;
    } else {
        if (guestBlock) guestBlock.classList.remove("js-hidden");
        if (heroBtn) heroBtn.href = "/login";
    }

    fetchPortofolio();
});

var allPhotos = [];

async function fetchPortofolio() {
    try {
        var res = await fetch("/api/portofolio-public");
        var data = await res.json();
        allPhotos = data.photos;

        if (!allPhotos.length) {
            var grid = document.getElementById("porto_grid");
            if (grid) grid.style.display = "none";
            return;
        }

        displayPhotos();
        setInterval(rotatePhotos, 15000);
    } catch (e) {
        console.error("[Portofolio]", e);
    }
}

function getRandomFour() {
    var pool = allPhotos.slice();
    while (pool.length < 4) pool = pool.concat(allPhotos);
    return pool
        .sort(function () {
            return Math.random() - 0.5;
        })
        .slice(0, 4);
}

function displayPhotos() {
    var selected = getRandomFour();
    var items = document.querySelectorAll(".porto-item");
    var imgs = document.querySelectorAll(".porto-img");

    items.forEach(function (item, i) {
        if (selected[i]) {
            imgs[i].src = selected[i].image_url;
            imgs[i].alt = selected[i].keterangan || "Portofolio ROUPAA";
            item.style.opacity = "1";
        }
    });
}

async function rotatePhotos() {
    var items = document.querySelectorAll(".porto-item");

    items.forEach(function (item) {
        item.style.opacity = "0";
    });

    await new Promise(function (resolve) {
        setTimeout(resolve, 500);
    });

    displayPhotos();
}

let __scrollY = 0;

window.openImageModal = function (src) {
    document.getElementById("modal_image_content").src = src;
    const downloadBtn = document.getElementById("btn_download_modal");
    if (downloadBtn) downloadBtn.href = src;

    const modal = document.getElementById("image_modal");
    if (modal) {
        __scrollY = window.scrollY;
        document.body.style.position = "fixed";
        document.body.style.top = `-${__scrollY}px`;
        document.body.style.width = "100%";

        modal.classList.remove("hidden");
        setTimeout(() => {
            modal.classList.remove("opacity-0");
            modal.children[1].children[0].classList.remove("scale-95");
        }, 10);
    }
};

window.closeImageModal = function () {
    const modal = document.getElementById("image_modal");
    if (modal) {
        modal.classList.add("opacity-0");
        modal.children[1].children[0].classList.add("scale-95");
        setTimeout(() => {
            modal.classList.add("hidden");
            document.body.style.position = "";
            document.body.style.top = "";
            document.body.style.width = "";
            window.scrollTo({ top: __scrollY, left: 0, behavior: "instant" });
        }, 300);
    }
};

document.addEventListener("DOMContentLoaded", function () {
    const grid = document.getElementById("porto_grid");
    if (grid) {
        grid.addEventListener("click", function (e) {
            const item = e.target.closest(".porto-item");
            if (!item) return;
            const img = item.querySelector(".porto-img");
            if (img && img.src) window.openImageModal(img.src);
        });
    }
});