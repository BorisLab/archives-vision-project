const modalForm = document.querySelector("#newFolder");

const form = document.querySelector("#create_folder");

const newFolderBtn = document.querySelector("#newFolderBtn");

const searchableInput = document.getElementById("searchable-select-input");

const selectField = document.querySelector("#create_folder_departement");

if (form) {
  if (form.querySelectorAll(".is-invalid").length > 0) {
    newFolderBtn.click();
    const selectedOption = selectField.options[selectField.selectedIndex];
    if (selectedOption && selectedOption.value !== "") {
      searchableInput.value = selectedOption.text;
    }
    // Rediriger vers la page de succès ou afficher un message de succès
  }
}
