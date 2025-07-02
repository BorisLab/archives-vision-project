document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll("#btn-req-folder").forEach((button) => {
    button.addEventListener("click", function() {
      const folderId = button.getAttribute("data-id");

      const reqFolderId = document.getElementById("reqDossierId");

      reqFolderId.value = folderId;

      fetch(`/folder/request-access`, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: new URLSearchParams({
          _action: "reqDossier",
          reqDossierId: folderId,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Met à jour le bouton pour indiquer la demande en attente
            button.disabled = true;
            button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-question-circle-fill mb-1 me-1" viewbox="0 0 16 16">
												<path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247m2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/>
											</svg> En attente... `;
            window.location.href = data.redirectUrl;
          }
        });
    });
  });
});
