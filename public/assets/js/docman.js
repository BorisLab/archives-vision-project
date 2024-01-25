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

const renameBtn = document.getElementById('rename');

// Ajoutez un écouteur d'événements pour afficher l'info-bulle (tooltip)
renameBtn.addEventListener('mouseover', () => {
  // Afficher l'info-bulle
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [... tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {trigger: 'hover'}))  
});

// Ajoutez un autre écouteur d'événements pour ouvrir la fenêtre modale
renameBtn.addEventListener('click', () => {
  // Ouvrir la fenêtre modale
  // Vous pouvez également utiliser Bootstrap pour cela
});

$(document).ready(function(){

  var quantity=0;
     $('.quantity-right-plus').click(function(e){
          
          // Stop acting like a button
          e.preventDefault();
          // Get the field name
          var quantity = parseInt($('#quantity').val());
          
          // If is not undefined
              
              $('#quantity').val(quantity + 1);
  
            
              // Increment
          
      });
  
       $('.quantity-left-minus').click(function(e){
          // Stop acting like a button
          e.preventDefault();
          // Get the field name
          var quantity = parseInt($('#quantity').val());
          
          // If is not undefined
        
              // Increment
              if(quantity>0){
              $('#quantity').val(quantity - 1);
              }
      });
      
  });