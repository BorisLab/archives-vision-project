const modalDep = document.querySelector("#newDepart");

const modalEditDep = document.querySelector("#editDepart");

const formCreateDep = document.querySelector("#departement_form");

const formEditDep = document.querySelector("#editDepForm");

const searchableList = document.getElementById('searchable-list');

const editSearchableList = document.getElementById('edit-searchable-list');

if(modalDep){

modalDep.addEventListener("hidden.bs.modal", function() {
  formCreateDep.reset();
  searchableList.classList.add("hidden");
});
}

if(modalEditDep){

modalEditDep.addEventListener("hidden.bs.modal", function() {
  formEditDep.reset();
  editSearchableList.classList.add("hidden");
});
}
