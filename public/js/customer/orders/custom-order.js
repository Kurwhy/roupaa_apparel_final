document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("order-form");
    if (!form) return;

    const errorContainer = document.getElementById("error-container");
    const errorList = document.getElementById("error-list");

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const token = sessionStorage.getItem("access_token");
        if (!token) {
            window.location.href = "/login";
            return;
        }

        errorContainer.classList.add("hidden");
        errorContainer.classList.remove("flex");
        errorList.innerHTML = "";

        const projectName = document.querySelector('input[name="project_name"]').value.trim();
        const productId = hiddenInput.value;
        const clientErrors = [];

        if (!projectName) {
            clientErrors.push("Nama project wajib diisi.");
        }
        if (!productId) {
            clientErrors.push("Jenis produk wajib dipilih.");
        }

        if (clientErrors.length > 0) {
            errorContainer.classList.remove("hidden");
            errorContainer.classList.add("flex");
            clientErrors.forEach((msg) => {
                const li = document.createElement("li");
                li.textContent = msg;
                errorList.appendChild(li);
            });
            return;
        }

        const formData = new FormData(this);
        const submitBtn = document.querySelector('button[form="order-form"]');
        const originalBtnContent = submitBtn.innerHTML;

        submitBtn.innerHTML =
            '<span class="material-symbols-outlined animate-spin">refresh</span> Memproses...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(this.action, {
                method: "POST",
                headers: {
                    Authorization: "Bearer " + token,
                    Accept: "application/json",
                },
                body: formData,
            });

            if (response.status === 401) {
                sessionStorage.removeItem("access_token");
                sessionStorage.removeItem("user_role");
                sessionStorage.removeItem("user_email");
                window.location.href = "/login";
                return;
            }

            const data = await response.json();

            if (response.ok) {
                window.location.href = "/customer/progress-pesanan";
            } else {
                errorContainer.classList.remove("hidden");
                errorContainer.classList.add("flex");

                if (data.errors) {
                    Object.values(data.errors)
                        .flat()
                        .forEach((msg) => {
                            const li = document.createElement("li");
                            li.textContent = msg;
                            errorList.appendChild(li);
                        });
                } else if (data.message) {
                    const li = document.createElement("li");
                    li.textContent = data.message;
                    errorList.appendChild(li);
                } else {
                    const li = document.createElement("li");
                    li.textContent = "Terjadi kesalahan tidak diketahui.";
                    errorList.appendChild(li);
                }
            }
        } catch (error) {
            console.error("Error:", error);
            errorContainer.classList.remove("hidden");
            errorContainer.classList.add("flex");
            const li = document.createElement("li");
            li.textContent = "Gagal terhubung ke server. Silakan coba lagi.";
            errorList.appendChild(li);
        } finally {
            submitBtn.innerHTML = originalBtnContent;
            submitBtn.disabled = false;
        }
    });

    const categories = window.appCategoriesData || [];
    const categoryRadios = document.querySelectorAll(".category-radio");

    const dropdownButton = document.getElementById("dropdown_button");
    const dropdownMenu = document.getElementById("dropdown_menu");
    const dropdownList = document.getElementById("dropdown_list");
    const dropdownLabel = document.getElementById("dropdown_label");
    const dropdownArrow = document.getElementById("dropdown_arrow");
    const hiddenInput = document.getElementById("hidden_product_id");

    const sumKategori = document.getElementById("summary_kategori");
    const sumProduk = document.getElementById("summary_produk");
    const sumProject = document.getElementById("summary_project_name");
    const sumReferensi = document.getElementById("summary_referensi");
    const previewList = document.getElementById("preview_attributes_list");

    const inputProjectName = document.querySelector(
        'input[name="project_name"]',
    );
    const inputNotes = document.querySelector('textarea[name="notes"]');

    const inputFile = document.getElementById("design_file_input");
    const uploadPlaceholder = document.getElementById("upload_placeholder");
    const previewContainer = document.getElementById("preview_container");
    const previewImage = document.getElementById("design_file_preview");

    let isDropdownOpen = false;

    inputProjectName.addEventListener("input", (e) => {
        sumProject.textContent = e.target.value || "-";
    });

    function updateReferensiSummary() {
        let status = [];
        if (inputFile && inputFile.files && inputFile.files.length > 0) {
            status.push("File Gambar Terlampir");
        }
        if (inputNotes.value.trim() !== "") {
            status.push("Catatan Ide Terisi");
        }

        if (status.length > 0) {
            sumReferensi.textContent = status.join(" & ");
            sumReferensi.classList.remove("opacity-70", "italic");
            sumReferensi.classList.add("text-primary");
        } else {
            sumReferensi.textContent = "Belum ada file/catatan";
            sumReferensi.classList.add("opacity-70", "italic");
            sumReferensi.classList.remove("text-primary");
        }
    }

    inputNotes.addEventListener("input", updateReferensiSummary);

    function resetPreview() {
        if (previewImage) previewImage.src = "";
        if (uploadPlaceholder) uploadPlaceholder.classList.remove("hidden");
        if (previewContainer) previewContainer.classList.add("hidden");
    }

    if (inputFile) {
        inputFile.addEventListener("change", function (event) {
            const file = event.target.files[0];

            if (file) {
                const validTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/jpg",
                    "image/webp",
                ];
                if (!validTypes.includes(file.type)) {
                    alert(
                        "Peringatan: Format file tidak didukung! Silakan upload gambar (JPG, PNG, atau WEBP).",
                    );
                    inputFile.value = "";
                    resetPreview();
                    updateReferensiSummary();
                    return;
                }

                const maxSize = 10 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert(
                        "Peringatan: Ukuran file terlalu besar! Maksimal 10MB.",
                    );
                    inputFile.value = "";
                    resetPreview();
                    updateReferensiSummary();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImage) previewImage.src = e.target.result;
                    if (uploadPlaceholder)
                        uploadPlaceholder.classList.add("hidden");
                    if (previewContainer)
                        previewContainer.classList.remove("hidden");
                };
                reader.readAsDataURL(file);

                updateReferensiSummary();
            } else {
                resetPreview();
                updateReferensiSummary();
            }
        });
    }

    function toggleDropdown() {
        isDropdownOpen = !isDropdownOpen;
        if (isDropdownOpen) {
            dropdownMenu.classList.remove(
                "opacity-0",
                "invisible",
                "translate-y-[-10px]",
            );
            dropdownArrow.classList.add("rotate-180");
            dropdownButton.classList.add("border-primary");
        } else {
            dropdownMenu.classList.add(
                "opacity-0",
                "invisible",
                "translate-y-[-10px]",
            );
            dropdownArrow.classList.remove("rotate-180");
            dropdownButton.classList.remove("border-primary");
        }
    }

    dropdownButton.addEventListener("click", toggleDropdown);
    document.addEventListener("click", function (event) {
        const isClickInside = document
            .getElementById("custom_dropdown_wrapper")
            .contains(event.target);
        if (!isClickInside && isDropdownOpen) toggleDropdown();
    });

    function renderProducts(categoryId) {
        dropdownList.innerHTML = "";
        hiddenInput.value = "";
        dropdownLabel.innerHTML = "-- Pilih Produk --";
        dropdownLabel.classList.remove("text-on-surface", "font-bold");
        dropdownLabel.classList.add("text-on-surface-variant", "opacity-70");
        sumProduk.textContent = "-";
        previewList.innerHTML =
            '<li class="text-xs text-on-surface-variant italic opacity-50">Silakan pilih produk terlebih dahulu...</li>';

        const category = categories.find((c) => c.id == categoryId);

        if (!category) return;
        sumKategori.textContent = category.name;

        if (!category.products || category.products.length === 0) {
            dropdownList.innerHTML =
                '<li class="px-4 py-3 text-on-surface-variant opacity-50 cursor-not-allowed">Belum ada produk di kategori ini</li>';
            dropdownButton.disabled = true;
            return;
        }

        dropdownButton.disabled = false;

        category.products.forEach((product) => {
            const li = document.createElement("li");
            li.className =
                "px-4 py-3 cursor-pointer hover:bg-primary/10 hover:text-primary transition-colors flex items-center gap-3";
            li.innerHTML = `<span class="material-symbols-outlined text-xl opacity-70">${product.icon || "inventory_2"}</span><span>${product.name}</span>`;

            li.addEventListener("click", () => {
                hiddenInput.value = product.id;
                dropdownLabel.innerHTML = product.name;
                dropdownLabel.classList.add("text-on-surface", "font-bold");
                dropdownLabel.classList.remove(
                    "text-on-surface-variant",
                    "opacity-70",
                );

                sumProduk.textContent = product.name;
                previewList.innerHTML = `
                    <li class="flex items-center justify-between text-[11px] text-on-surface font-semibold bg-surface-container-high p-2 rounded-sm">
                        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[14px] text-primary">tag</span> Kuantitas Total</div>
                    </li>
                `;

                if (product.attributes && product.attributes.length > 0) {
                    product.attributes.forEach((attr) => {
                        previewList.innerHTML += `
                            <li class="flex items-center gap-2 text-[11px] text-on-surface bg-surface-container-high p-2 rounded-sm">
                                <span class="material-symbols-outlined text-[14px] text-primary">check_circle</span>
                                ${attr.name}
                            </li>
                        `;
                    });
                }

                toggleDropdown();
            });
            dropdownList.appendChild(li);
        });
    }

    categoryRadios.forEach((radio) => {
        radio.addEventListener("change", (e) => {
            renderProducts(e.target.value);
            if (!isDropdownOpen) setTimeout(toggleDropdown, 150);
        });
    });

    const checkedCategory = document.querySelector(".category-radio:checked");
    if (checkedCategory) {
        checkedCategory.dispatchEvent(new Event("change"));
        if (isDropdownOpen) toggleDropdown();
    }
});
