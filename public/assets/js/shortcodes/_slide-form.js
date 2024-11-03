document.addEventListener("DOMContentLoaded", function() {
  const container = document.querySelector("#form-container");
  const btnPhys = document.querySelector("#formPhys");
  const btnNum = document.querySelector("#formNum");
  const phys = document.querySelector("#add_phys_file");
  const num = document.querySelector("#upload_fichier");
  const choice = document.querySelector("#form-choice");
  const modalFiles = document.querySelector("#addFile");

  phys.style.display = "none";
  num.style.display = "none";

  btnPhys.addEventListener("change", function(e) {
    phys.style.display = "block";
    num.style.display = "none";
    if (!container.contains(phys)) {
      container.append(phys);
    }
    choice.style.display = "none";
  });

  btnNum.addEventListener("change", function(e) {
    num.style.display = "block";
    phys.style.display = "none";
    if (!container.contains(num)) {
      container.append(num);
    }
    choice.style.display = "none";
  });

  // Rétablir le formulaire
  modalFiles.addEventListener("hidden.bs.modal", function() {
    phys.style.display = "none";
    num.style.display = "none";
    choice.style.display = "block";
    btnPhys.checked = false;
    btnNum.checked = false;
  });
});
