document.addEventListener("DOMContentLoaded", function() {
  //Dossiers
  const editDep = document.getElementById("editDepart");
  const delDep = document.getElementById("deleteDepart");

  if (editDep) {
    editDep.addEventListener("show.bs.modal", function(event) {
      let depButton = event.relatedTarget;
      let depId = depButton.getAttribute("data-id");
      let depName = depButton.getAttribute("data-name");
      let depPar = depButton.getAttribute("data-p");
      let depParId = depButton.getAttribute("data-p-i");
      let modalBodyDepInput = document.getElementById("editDepName");
      let modalBodyDepId = document.getElementById("editDepId");
      let modalBodyDepPar = document.getElementById(
        "edit-searchable-select-input"
      );
      modalBodyDepInput.value = depName;
      modalBodyDepId.value = depId;
      modalBodyDepPar.value = depPar;

      const select = document.getElementById('edit-departement_departement_parent');
      const allOptions = Array.from(select.querySelectorAll('option[data-id]'));

      select.innerHTML = '<option value=""></option>'; // reset

      allOptions.forEach(opt => {
          if (opt.dataset.id !== depId) {
              const newOpt = document.createElement('option');
              newOpt.value = opt.value;
              newOpt.textContent = opt.textContent;
              newOpt.dataset.id = opt.dataset.id;
              if (opt.value === depParId) newOpt.selected = true;
              select.appendChild(newOpt);
          }
      });

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

      var editDepForm = document.getElementById("editDepForm");
      editDepForm.action = editDepForm.action.replace(/\/\d+$/, "/" + depId);
    });
  }

  if (delDep) {
    delDep.addEventListener("show.bs.modal", function(event) {
      let depButton = event.relatedTarget;
      let depId = depButton.getAttribute("data-id");
      let depName = depButton.getAttribute("data-name");
      let modalBodyDelDep = document.getElementById("depToDelName");
      let modalBodyDepId = document.getElementById("delDepId");
      modalBodyDelDep.innerHTML = depName;
      modalBodyDepId.value = depId;

      let delDepForm = document.getElementById("delDepForm");
      delDepForm.action = delDepForm.action.replace(/\/\d+$/, "/" + depId);
    });
  }
});
