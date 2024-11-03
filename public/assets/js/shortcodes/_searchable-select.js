// public/js/searchable-select.js

document.addEventListener("DOMContentLoaded", function() {
  const searchableInput = document.getElementById("searchable-select-input");
  const searchableList = document.getElementById("searchable-list");
  const searchableSelectList = document.querySelector(
    ".searchable-select-list"
  );

  const selectField = document.querySelector("#dossier_departement");

  // Récupérer toutes les options provenant du champ <select> masqué du formBuilder
  const options = [];
  document.querySelectorAll(".searchable-select option").forEach((option) => {
    if (option.value) {
      options.push({ value: option.value, label: option.textContent });
    }
  });

  // La fonction de filtre des résultats
  function filterOptions() {
    const searchTerm = searchableInput.value.toLowerCase();
    searchableList.innerHTML = "";

    if (searchTerm === "") {
      searchableList.classList.add("hidden");
      return; // Ne rien afficher si le champ de saisie est vide
    }

    const filteredOptions = options.filter((option) =>
      option.label.toLowerCase().includes(searchTerm)
    );
    if (filteredOptions.length > 0) {
      filteredOptions.forEach((option) => {
        const listItem = document.createElement("li");
        listItem.className = "list-group-item";
        listItem.textContent = option.label;
        listItem.dataset.value = option.value;
        listItem.addEventListener("click", () => {
          searchableInput.value = option.label;
          searchableSelectList.value = option.value;
          searchableList.innerHTML = "";
          searchableList.classList.add("hidden");
        });
        searchableList.appendChild(listItem);
      });
      searchableList.classList.remove("hidden");
    } else {
      const noResultItem = document.createElement("li");
      noResultItem.className = "list-group-item text-muted";
      noResultItem.textContent = "Résultat introuvable";
      searchableList.appendChild(noResultItem);
      searchableList.classList.remove("hidden");
    }
  }
  searchableInput.addEventListener("input", filterOptions);
  //searchableInput.addEventListener('blur', () => { searchableList.classList.add('hidden')});

  // Initial populate
  filterOptions();
});
