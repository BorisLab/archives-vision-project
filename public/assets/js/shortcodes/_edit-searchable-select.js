// public/js/searchable-select.js

document.addEventListener("DOMContentLoaded", function() {
  const editSearchableInput = document.getElementById(
    "edit-searchable-select-input"
  );
  const editSearchableList = document.getElementById("edit-searchable-list");
  const editSearchableSelectList = document.querySelector(
    ".edit-searchable-select-list"
  );

  // Récupérer toutes les options provenant du champ <select> masqué du formBuilder
  const options = [];
  document
    .querySelectorAll(".edit-searchable-select option")
    .forEach((option) => {
      if (option.value) {
        options.push({ value: option.value, label: option.textContent });
      }
    });

  // La fonction de filtre des résultats
  function filterOptions() {
    const searchTerm = editSearchableInput.value.toLowerCase();
    editSearchableList.innerHTML = "";

    if (searchTerm === "") {
      editSearchableList.classList.add("hidden");
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
          editSearchableInput.value = option.label;
          editSearchableSelectList.value = option.value;
          editSearchableList.innerHTML = "";
          editSearchableList.classList.add("hidden");
        });
        editSearchableList.appendChild(listItem);
      });
      editSearchableList.classList.remove("hidden");
    } else {
      const noResultItem = document.createElement("li");
      noResultItem.className = "list-group-item text-muted";
      noResultItem.textContent = "Résultat introuvable";
      editSearchableList.appendChild(noResultItem);
      editSearchableList.classList.remove("hidden");
    }
  }
  editSearchableInput.addEventListener("input", filterOptions);

  // Initial populate
  filterOptions();
});
