document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const statusSelect = document.getElementById("statutSelect");
    const dateMin = document.getElementById("dateMin");
    const dateMax = document.getElementById("dateMax");
    const tableRows = document.querySelectorAll("tbody tr");
    const tableBody = document.querySelector("#body-md");
    let noResultMessage = document.createElement("tr");

    noResultMessage.innerHTML = `<td colspan="10" class="text-center">Aucun résultat trouvé</td>`;
    noResultMessage.style.display = "none";
    tableBody.appendChild(noResultMessage);

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedStatus = statusSelect.value.toLowerCase().trim();
        const minDate = dateMin.value ? new Date(dateMin.value) : null;
        const maxDate = dateMax.value ? new Date(dateMax.value) : null;
        let hasVisibleRow = false;

        tableRows.forEach((row) => {
            let textMatch = false;
            let statusMatch = false;
            let dateMatch = false;

            const columns = row.querySelectorAll("td");
            const statusText = row.querySelector(".badge")?.textContent.toLowerCase().trim();
            const dateAccess = row.querySelector("td:nth-child(8) time")?.textContent.trim(); // Colonne "accessReq"
            const endDate = row.querySelector("td:nth-child(9) time")?.textContent.trim(); // Colonne "Fin d'accès"

            // Vérifier la correspondance du texte dans toute la ligne
            textMatch = searchTerm === "" || Array.from(columns).some(td => td.textContent.toLowerCase().includes(searchTerm));

            // Vérifier la correspondance du statut
            statusMatch = selectedStatus === "choisir" || (statusText && statusText.includes(selectedStatus));

            // Vérifier la correspondance des dates
            let rowDate = dateAccess ? new Date(dateAccess.split(",")[0].trim().split("/").reverse().join("-")) : null;
            let rowEndDate = endDate ? new Date(endDate.split(",")[0].trim().split("/").reverse().join("-")) : null;

            if (!minDate && !maxDate) {
                dateMatch = true;
            } else if (minDate && !maxDate) {
                dateMatch = rowDate && rowDate >= minDate;
            } else if (!minDate && maxDate) {
                dateMatch = rowDate && rowDate <= maxDate;
            } else if (minDate && maxDate) {
                dateMatch = rowDate && rowDate >= minDate && rowDate <= maxDate;
            }

            // Vérifier si la ligne doit être affichée
            if (textMatch && statusMatch && dateMatch) {
                row.style.display = "";
                hasVisibleRow = true;
            } else {
                row.style.display = "none";
            }
        });

        // Gérer l'affichage du message "Aucun résultat trouvé"
        noResultMessage.style.display = hasVisibleRow ? "none" : "";
    }

    // Écouteurs d'événements
    searchInput.addEventListener("input", filterTable);
    statusSelect.addEventListener("change", filterTable);
    dateMin.addEventListener("input", filterTable);
    dateMax.addEventListener("input", filterTable);
});
