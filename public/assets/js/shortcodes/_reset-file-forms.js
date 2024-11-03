const modalFilesAdd = document.querySelector("#addFile");

const keywordsFileContainer = document.getElementById(
  "addFileKeywordsContainer"
);

const btnPhys = document.querySelector("#formPhys");
const btnNum = document.querySelector("#formNum");
const phys = document.querySelector("#add_phys_file");
const num = document.querySelector("#upload_fichier");
const choice = document.querySelector("#form-choice");

modalFilesAdd.addEventListener("hidden.bs.modal", function() {
  phys.reset();
  num.reset();
  document.querySelector("#list-group").innerHTML = "";
  firstKeyword = keywordsFileContainer.firstElementChild;
  while (keywordsFileContainer.children.length > 1) {
    keywordsFileContainer.removeChild(firstKeyword);
    firstKeyword = keywordsFileContainer.firstElementChild;
  }
});
