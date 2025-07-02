document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("user-searchbar");
    const userList = document.querySelectorAll(".user-unit");
    let uContain = document.querySelector(".list-group");
    let noFind = document.createElement("div");
    noFind.innerHTML = "Aucun correspondant trouvé";
    noFind.className = "text-center text-muted d-none"; // Caché par défaut
  
    // Ajouter le message au container, mais caché
    uContain.appendChild(noFind);
  
    searchInput.addEventListener("input", function() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      let found = false; // Flag pour vérifier s'il y a des correspondances
  
      userList.forEach((user) => {
        const userName = user.querySelector(".name span").textContent.toLowerCase();
  
        if (searchTerm === "" || userName.includes(searchTerm)) {
          user.style.display = "";
          found = true; // Une correspondance a été trouvée
        } else {
          user.style.display = "none"; 
        }
      });
  
      // Afficher ou cacher le message d'absence de résultat
      if (found) {
        noFind.classList.add("d-none"); // Cacher le message si un élément est trouvé
      } else {
        noFind.classList.remove("d-none"); // Afficher le message sinon
      }
    });
  });
  