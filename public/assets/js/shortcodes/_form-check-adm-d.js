const modalForm = document.querySelector("#newDepart");

const form = document.querySelector("#create_departement");

const newDepBtn = document.querySelector("#newDepBtn");

const searchableInput = document.getElementById("searchable-select-input");

const selectField = document.querySelector("#create_departement_departement_parent");

if (form) {
  if (form.querySelectorAll(".is-invalid").length > 0) {
    newDepBtn.click();
    const selectedOption = selectField.options[selectField.selectedIndex];
    if (selectedOption && selectedOption.value !== "") {
      searchableInput.value = selectedOption.text;
    }
    // Rediriger vers la page de succès ou afficher un message de succès
  }
}
