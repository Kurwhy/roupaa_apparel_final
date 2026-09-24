document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("form_estimasi_harga");
    if (!form) return; 

    const sablonInputs = form.querySelectorAll(".sablon-input");
    const subtotalCells = form.querySelectorAll(
        "[data-item-row] .subtotal-item",
    );
    const summaryBaju = document.getElementById("summary_baju");
    const summarySablon = document.getElementById("summary_sablon");
    const summaryTotal = document.getElementById("summary_total");

    function formatRp(angka) {
        return "Rp " + Math.round(angka).toLocaleString("id-ID");
    }

    function hitungUlang() {
        let totalBaju = 0;
        let totalSablon = 0;

        const rows = form.querySelectorAll("[data-item-row]");

        rows.forEach((row, idx) => {
            const hargaBaju = parseFloat(row.dataset.hargaBaju) || 0;
            const qty = parseInt(row.dataset.qty) || 1;
            const inputSablon = row.querySelector(".sablon-input");
            const biayaSablon = parseFloat(inputSablon?.value) || 0;

            const subtotalItem = (hargaBaju + biayaSablon) * qty;

            const subtotalCell = row.querySelector(".subtotal-item");
            if (subtotalCell) subtotalCell.textContent = formatRp(subtotalItem);

            totalBaju += hargaBaju * qty;
            totalSablon += biayaSablon * qty;
        });

        const grandTotal = totalBaju + totalSablon;

        if (summaryBaju) summaryBaju.textContent = formatRp(totalBaju);
        if (summarySablon) summarySablon.textContent = formatRp(totalSablon);
        if (summaryTotal) summaryTotal.textContent = formatRp(grandTotal);
    }

    sablonInputs.forEach((input) => {
        input.addEventListener("input", hitungUlang);
    });

    hitungUlang();

    form.addEventListener("submit", function (e) {
        const total = summaryTotal?.textContent ?? "?";
        const ok = confirm(
            `Kirim estimasi ke customer?\n\nTotal: ${total}\n\nSetelah dikirim, customer akan mendapat notifikasi via chat.`,
        );
        if (!ok) e.preventDefault();
    });
});
