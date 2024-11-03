const modalFolder = document.querySelector("#newFolder");

const modalEditFolder = document.querySelector("#editFolder");

const formCreateFolder = document.querySelector("#dossier_form");

const formEditFolder = document.querySelector("#editFolderForm");

const keywordsContainer = document.getElementById("addFolderKeywordsContainer");

const searchableList = document.getElementById('searchable-list');

const editSearchableList = document.getElementById('edit-searchable-list');


modalFolder.addEventListener("hidden.bs.modal", function() {
  formCreateFolder.reset();
  searchableList.classList.add("hidden");
  firstKeyword = keywordsContainer.firstElementChild;
  while(keywordsContainer.children.length > 1){
    keywordsContainer.removeChild(firstKeyword);
    firstKeyword = keywordsContainer.firstElementChild;
  }
});

modalEditFolder.addEventListener("hidden.bs.modal", function() {
  formEditFolder.reset();
  editSearchableList.classList.add("hidden");
})
