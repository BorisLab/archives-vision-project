document.addEventListener("DOMContentLoaded", function() {
  document.getElementById("uploadButton").addEventListener("click", function() {
    document.getElementById("photoProfilInput").click();
  });

  document
    .getElementById("photoProfilInput")
    .addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file) {
        const formData = new FormData();
        formData.append("photoProfil", file);

        fetch("/profile/update-photo", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              if (document.getElementById("profileImage")) {
                document.getElementById("profileImage").src = data.newImageUrl;
              }
              location.reload();
            } else {
              alert("Erreur lors du téléversement de l'image.");
            }
          })
          .catch((error) => console.error("Erreur:", error));
      }
    });
});
