document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('searchInput');
	const nbrSelect = document.getElementById('nbrSelect');
	const dateMin = document.getElementById('dateMin');
	const dateMax = document.getElementById('dateMax');
	const tableRows = document.querySelectorAll('tbody tr');
  const tableBody = document.querySelector('#body-md');
	const emptyMessage = document.createElement("tr");

  emptyMessage.innerHTML = `<td colspan="10" class="text-center">Aucun résultat trouvé</td>`;
  emptyMessage.style.display = "none";
  tableBody.appendChild(emptyMessage);

	function filterTable() {
		const searchTerm = searchInput.value.trim().toLowerCase();
		const selectedRange = nbrSelect.value;
		const minDate = dateMin.value ? new Date(dateMin.value) : null;
		const maxDate = dateMax.value ? new Date(dateMax.value) : null;

    let hasVisibleRow = false;

		tableRows.forEach(row => {
			const cells = row.querySelectorAll('td');
			const deptName = cells[0]?.textContent.toLowerCase();
			const staffSize = parseInt(cells[2]?.textContent);
			const createdAt = new Date(cells[3]?.textContent);

			let matchesSearch = !searchTerm || deptName.includes(searchTerm);
			let matchesRange = true;
			let matchesDate = true;

			// Filtrage par taille du personnel
			if (selectedRange === '0 - 50') {
				matchesRange = staffSize >= 0 && staffSize <= 50;
			} else if (selectedRange === '50 - 100') {
				matchesRange = staffSize > 50 && staffSize <= 100;
			} else if (selectedRange === 'Plus de 100') {
				matchesRange = staffSize > 100;
			}

			// Filtrage par date de création
			if (minDate && maxDate && minDate > maxDate) {
				matchesDate = false;
			} else {
				if (minDate) matchesDate = createdAt >= minDate;
				if (maxDate) matchesDate = matchesDate && createdAt <= maxDate;
			}

			const showRow = matchesSearch && matchesRange && matchesDate;

			if (showRow) {
				row.style.display = '';
        hasVisibleRow = true;
			} else {
				row.style.display = 'none';
			}
		});

		// Affichage ou masquage du message "Aucun résultat"
		if (hasVisibleRow === false && (searchTerm || selectedRange !== 'Choisir' || dateMin.value || dateMax.value)) {
			emptyMessage.style.display = '';
		} else {
			emptyMessage.style.display = 'none';
		}
	}

	searchInput.addEventListener('input', filterTable);
	nbrSelect.addEventListener('change', filterTable);
	dateMin.addEventListener('change', filterTable);
	dateMax.addEventListener('change', filterTable);
});