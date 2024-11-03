document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("previewFile");
  const modalContent = document.getElementById("filePreviewContent");
  const modalTitle = document.querySelector("#previewTitle");

  modal.addEventListener("show.bs.modal", function(event) {
    const button = event.relatedTarget;
    const fileId = button.getAttribute("data-id");
    const fileTitle = button.getAttribute("data-name");

    // Fetch the file preview URL (from Symfony route)
    fetch(`/file/preview/${fileId}`)
      .then((response) => {
        // Check the content type to decide how to display the file
        const contentType = response.headers.get("Content-Type");

        if (contentType.includes("image")) {
          modalTitle.innerHTML = `${fileTitle}`;
          modalContent.innerHTML = `<img src="/file/preview/${fileId}" alt="Image preview" style="max-width:60vw;max-height:40vw;"/>`;
        } else if (contentType.includes("audio")) {
          modalTitle.innerHTML = `${fileTitle}`;
          modalContent.innerHTML = `<audio controls style="justify-content:center;align-items:center;max-width:40vw;">
                                        <source src="/file/preview/${fileId}" type="${contentType}">
                                        Your browser does not support the audio element.
                                    </audio>`;
        } else if (contentType.includes("pdf")) {
          modalTitle.innerHTML = `${fileTitle}`;
          modalContent.innerHTML = `<iframe src="/file/preview/${fileId}" style="width:100%;height:40vw"></iframe>`;
        } else if (contentType.includes("video")) {
          modalTitle.innerHTML = `${fileTitle}`;
          modalContent.innerHTML = `<video controls style="justify-content:center;align-items:center;max-width:60vw;max-height:40vw;">
                                        <source src="/file/preview/${fileId}" type="${contentType}">
                                        Your browser does not support the video element.
                                    </video>`;
        } else {
          modalTitle.innerHTML = `${fileTitle}`;
          modalContent.innerHTML = `<p class="text-center text-red-500">Prévisualisation non disponible pour ce type de fichier.</p>`;
        }

        // Show the modal
        modal.classList.remove("hidden");
      })
      .catch((error) => {
        console.error("Error fetching the file preview:", error);
      });
  });

  modal.addEventListener("hide.bs.modal", function() {
    modalContent.innerHTML = ""; // Clear the content when the modal is closed
  });
});
