function openLegalModal(modalId) {
    const modal = document.getElementById(modalId);
    const modalContent = modal.querySelector(".modal-content");

    modal.classList.remove("hidden");

    setTimeout(() => {
        modal.classList.remove("opacity-0");
        modalContent.classList.remove("scale-95");
        modalContent.classList.add("scale-100");
    }, 10);
}

function closeLegalModal(modalId) {
    const modal = document.getElementById(modalId);
    const modalContent = modal.querySelector(".modal-content");

    modal.classList.add("opacity-0");
    modalContent.classList.remove("scale-100");
    modalContent.classList.add("scale-95");

    setTimeout(() => {
        modal.classList.add("hidden");
    }, 300);
}

window.addEventListener("click", function (event) {
    const modalTerms = document.getElementById("modal-terms");
    const modalPrivacy = document.getElementById("modal-privacy");

    if (event.target === modalTerms) closeLegalModal("modal-terms");
    if (event.target === modalPrivacy) closeLegalModal("modal-privacy");
});
