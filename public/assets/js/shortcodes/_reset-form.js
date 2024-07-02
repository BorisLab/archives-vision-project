const modal = document.querySelector("#newFolder");

const closeBtn = document.querySelector("#newFolder .btn-close");

const cancelBtn = document.querySelector("#create-cancel-button");

const formCreateFolder = document.querySelector("#create_folder");

const keywordsContainer = document.getElementById("keywordsContainer");

const keywords = keywordsContainer.querySelectorAll(".keyword");


closeBtn.addEventListener("click", function() {
  formCreateFolder.reset();
  firstKeyword = keywordsContainer.firstElementChild;
  while(keywordsContainer.children.length > 1){
    keywordsContainer.removeChild(firstKeyword);
    firstKeyword = keywordsContainer.firstElementChild;
  }
});

cancelBtn.addEventListener("click", function() {
  formCreateFolder.reset();
  firstKeyword = keywordsContainer.firstElementChild;
  while(keywordsContainer.children.length > 1){
    keywordsContainer.removeChild(firstKeyword);
    firstKeyword = keywordsContainer.firstElementChild;
  }
});