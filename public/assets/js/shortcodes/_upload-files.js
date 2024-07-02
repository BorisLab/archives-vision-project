document.addEventListener("DOMContentLoaded", function() {
  const dropZone = document.getElementById("drop-zone");
  const fileInput = document.getElementById("add_fichier_fichier");
  const fileList = document.getElementById("file-list");
  const form = document.getElementById("upload_fichier");
  let activeUploads = new Map();

  dropZone.addEventListener("dragover", function(e) {
    e.preventDefault();
    dropZone.classList.add("bg-light");
  });

  dropZone.addEventListener("dragleave", function() {
    dropZone.classList.remove("bg-light");
  });

  dropZone.addEventListener("drop", function(e) {
    e.preventDefault();
    dropZone.classList.remove("bg-light");
    const files = e.dataTransfer.files;
    fileInput.files = files;
    handleFiles(files);
  });

  fileInput.addEventListener("change", function() {
    handleFiles(fileInput.files);
  });

  form.addEventListener("submit", function(e) {
    e.preventDefault();
    uploadFiles(fileInput.files);
  });

  function handleFiles(files) {
    fileList.innerHTML = "";
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      const fileElement = document.createElement("div");
      fileElement.classList.add("file-item");
      fileElement.setAttribute("data-file", file.name);
      fileElement.innerHTML = `
                <div class="d-flex align-items-center">
                    <span class="badge badge-secondary">${file.type
                      .split("/")[1]
                      .toUpperCase()}</span>
                    <span class="ml-2">${file.name}</span>
                    <span class="ml-auto">Uploading...</span>
                    <button type="button" class="btn btn-danger btn-sm ml-2 abort-upload">&times;</button>
                </div>
                <div class="progress mt-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
            `;
      fileList.appendChild(fileElement);
    }
  }

  function uploadFiles(files) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      const formData = new FormData();
      formData.append("file[file]", file);

      const xhr = new XMLHttpRequest();
      xhr.open("POST", form.action, true);

      xhr.upload.addEventListener("progress", function(e) {
        if (e.lengthComputable) {
          const percentComplete = (e.loaded / e.total) * 100;
          updateProgress(file.name, percentComplete);
        }
      });

      xhr.addEventListener("load", function() {
        if (xhr.status === 200) {
          markAsCompleted(file.name);
        } else {
          markAsError(file.name);
        }
      });

      xhr.addEventListener("error", function() {
        markAsError(file.name);
      });

      xhr.addEventListener("abort", function() {
        markAsCancelled(file.name);
      });

      activeUploads.set(file.name, xhr);

      const abortButton = document.querySelector(
        `[data-file="${file.name}"] .abort-upload`
      );
      abortButton.addEventListener("click", function() {
        xhr.abort();
      });

      xhr.send(formData);
    }
  }

  function updateProgress(fileName, percent) {
    const fileElement = document.querySelector(
      `[data-file="${fileName}"] .progress-bar`
    );
    fileElement.style.width = `${percent}%`;
  }

  function markAsCompleted(fileName) {
    const fileElement = document.querySelector(`[data-file="${fileName}"]`);
    fileElement.querySelector(".ml-auto").innerText = "Completed";
    fileElement.querySelector(".abort-upload").remove();
  }

  function markAsError(fileName) {
    const fileElement = document.querySelector(`[data-file="${fileName}"]`);
    fileElement.querySelector(".ml-auto").innerText = "Error";
    fileElement.querySelector(".abort-upload").remove();
  }

  function markAsCancelled(fileName) {
    const fileElement = document.querySelector(`[data-file="${fileName}"]`);
    fileElement.querySelector(".ml-auto").innerText = "Cancelled";
    fileElement.querySelector(".abort-upload").remove();
  }
});
