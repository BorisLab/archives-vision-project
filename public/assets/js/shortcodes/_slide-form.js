const slidePage = document.querySelector(".slidepage");

const firstNextBtn = document.querySelector("#nextBtn");

const prevBtnSD = document.querySelector("#prevbtnSD");
const submitBtnSD = document.querySelector("#submitbtnSD");

const prevBtnFIC = document.querySelector("#prevbtnFIC");
const submitBtnFIC = document.querySelector("#submitbtnFIC");

firstNextBtn.addEventListener("click", function() {
  slidePage.classList.add("hidden");
  slidePage.style.marginLeft = "-33%";
});

prevBtnSD.addEventListener("click", function() {
  slidePage.classList.remove("hidden");
  slidePage.style.marginLeft = "0%";
});

submitBtnSD.addEventListener("click", function() {
  slidePage.style.marginLeft = "-66%";
});

prevBtnFIC.addEventListener("click", function() {
  slidePage.classList.remove("hidden");
  slidePage.style.marginLeft = "0%";
});
