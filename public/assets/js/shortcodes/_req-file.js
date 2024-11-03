document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll("#btn-req-file").forEach((button) => {
    button.addEventListener("click", function() {
      const fileId = button.getAttribute("data-id");

      fetch(`/file/request-access/${fileId}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Met à jour le bouton pour indiquer la demande en attente
            button.disabled = true;
            button.innerText = "En attente...";
          }
        });
    });
  });
});
