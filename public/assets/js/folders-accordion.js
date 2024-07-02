// JavaScript pour le comportement de l'accordéon

//const accordionBtn = document.querySelector('.accordion-btn');
const buttons = document.querySelectorAll('.accordion-btn');

const subbuttons = document.querySelectorAll('.accordion-sub');

//const accordionExp = document.querySelector('.accordion-exp');
const accordionExps = document.querySelectorAll('.accordion-exp');
const subExps = document.querySelectorAll('.sub-exp');

buttons.forEach((button) => {

  elementExp = button.lastElementChild;
  btnExp = elementExp.firstElementChild;

  btnExp.addEventListener("click", () => {
    // Trouvez le contenu principal associé au bouton cliqué
    const content = button.nextElementSibling;

    // Basculer la visibilité du contenu principal
    if (content.style.display === "block") {
      content.style.display = "none";
      button.style.backgroundColor = "white";
    } else {
      content.style.display = "block";
      content.scrollIntoView({ behavior: "smooth" });
      button.style.backgroundColor = "#9FD3BF";
    }
  });
});

subbuttons.forEach((subbutton) => {

  subelementExp = subbutton.lastElementChild;
  subbtnExp = subelementExp.firstElementChild;

  subbtnExp.addEventListener("click", () => {
    // Trouvez le contenu principal associé au bouton cliqué
    const subcontent = subbutton.nextElementSibling;

    // Basculer la visibilité du contenu principal
    if (subcontent.style.display === "block") {
      subcontent.style.display = "none";
      subbutton.style.backgroundColor = "white";
    } else {
      subcontent.style.display = "block";
      subcontent.scrollIntoView({ behavior: "smooth" });
      subbutton.style.backgroundColor = "#78D3DF";
    }
  });
});