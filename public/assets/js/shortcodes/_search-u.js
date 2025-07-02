document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("searchInput");
  const typeSelect = document.getElementById("type-select");
  const formatSelect = document.getElementById("format-select");
  const dateMin = document.getElementById("dateMin");
  const dateMax = document.getElementById("dateMax");
  const resultContainer = document.createElement("div");

  resultContainer.className = "search-results";
  searchInput.parentNode.appendChild(resultContainer);
  resultContainer.classList.add("rounded-1");
  resultContainer.classList.add("d-none");

  function fetchResults() {
    let query = searchInput.value.trim();
    if (query.length < 2) {
      resultContainer.innerHTML = "";
      resultContainer.classList.add("d-none");
      return;
    }

    resultContainer.classList.remove("d-none");

    let params = new URLSearchParams({
      q: query,
      type: typeSelect.value,
      format: formatSelect.value,
      dateMin: dateMin.value,
      dateMax: dateMax.value,
    });

    fetch(`/docficu/recherche?${params.toString()}`)
      .then((response) => response.json())
      .then((data) => {
        resultContainer.innerHTML = "";
        if (data.dossiers.length === 0 && data.fichiers.length === 0) {
          resultContainer.innerHTML = `<p class="text-center mt-3">Aucun résultat trouvé.</p>`;
          return;
        }

        data.dossiers.forEach((dossier) => {
          let div = document.createElement("div");
          let a = document.createElement("a");
          div.className = "search-item";
          a.innerHTML = `<svg style="padding-bottom:3px;padding-right:5px" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-folder-fill" viewbox="0 0 16 16">
                                 <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3m-8.322.12q.322-.119.684-.12h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981z"/>
                             </svg><strong>Dossier :</strong> ${dossier.n_dossier} (${dossier.format})`;
          a.href = `/user/${dossier.id}/répertoire`;
          a.style.color = "black";
          div.appendChild(a);
          resultContainer.appendChild(div);
        });

        data.fichiers.forEach((fichier) => {
          let div = document.createElement("div");
          let a = document.createElement("a");
          div.className = "search-item";
          if (fichier.type === "Document") {
            a.innerHTML = `<svg style="padding-bottom:3px;padding-right:5px" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-file-earmark-text" viewbox="0 0 16 16">
                                  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                               </svg><strong>Fichier :</strong> ${fichier.n_fichier} (${fichier.format}, ${fichier.type})`;
          } else if (fichier.type === "Image") {
            a.innerHTML = `<svg style="padding-bottom:3px;padding-right:5px" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-file-earmark-image" viewbox="0 0 16 16">
                                      <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                      <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                               </svg><strong>Fichier :</strong> ${fichier.n_fichier} (${fichier.format}, ${fichier.type})`;
          } else if (fichier.type === "Vidéo") {
            a.innerHTML = `<svg style="padding-bottom:3px;padding-right:5px" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-file-earmark-play" viewbox="0 0 16 16">
                                      <path d="M6 6.883v4.234a.5.5 0 0 0 .757.429l3.528-2.117a.5.5 0 0 0 0-.858L6.757 6.454a.5.5 0 0 0-.757.43z"/>
                                      <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                               </svg><strong>Fichier :</strong> ${fichier.n_fichier} (${fichier.format}, ${fichier.type})`;
          } else if (fichier.type === "Audio") {
            a.innerHTML = `<svg style="padding-bottom:3px;padding-right:5px" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-file-earmark-music" viewbox="0 0 16 16">
                                      <path d="M11 6.64a1 1 0 0 0-1.243-.97l-1 .25A1 1 0 0 0 8 6.89v4.306A2.6 2.6 0 0 0 7 11c-.5 0-.974.134-1.338.377-.36.24-.662.628-.662 1.123s.301.883.662 1.123c.364.243.839.377 1.338.377s.974-.134 1.338-.377c.36-.24.662-.628.662-1.123V8.89l2-.5z"/>
                                      <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                               </svg><strong>Fichier :</strong> ${fichier.n_fichier} (${fichier.format}, ${fichier.type})`;
          }
          a.href = `/user/${fichier.parentId}/répertoire`;
          a.style.color = "black";
          div.appendChild(a);
          resultContainer.appendChild(div);
        });
      });
  }

  searchInput.addEventListener("input", fetchResults);
  typeSelect.addEventListener("change", fetchResults);
  formatSelect.addEventListener("change", fetchResults);
  dateMin.addEventListener("change", fetchResults);
  dateMax.addEventListener("change", fetchResults);
});
