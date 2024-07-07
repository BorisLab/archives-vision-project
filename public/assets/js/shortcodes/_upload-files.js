document.addEventListener("DOMContentLoaded", function() {
  const dropZone = document.getElementById("drop-zone");
  const fileBrowseBtn = document.querySelector(".file-browse");
  const fileInput = document.getElementById("add_fichier_fichiers");
  const fileList = document.querySelector("#list-group");
  const form = document.getElementById("upload_fichier");

  const showIconItem = (ext) => {
    const picsExtArray = ["jpg", "jpeg", "png", "apng", "gif", "svg"];
    const audsExtArray = ["mp3", "wav", "aiff", "alac"];
    const vidsExtArray = ["mp4", "mkv", "avi", "mov"];
    const docsExtArray = ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx"];
    if (picsExtArray.includes(ext.toLowerCase())) {
      return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-image" viewbox="0 0 16 16">
				<path d="M8.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
				<path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M3 2a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v8l-2.083-2.083a.5.5 0 0 0-.76.063L8 11 5.835 9.7a.5.5 0 0 0-.611.076L3 12z"/>					
              </svg>`;
    } else if (audsExtArray.includes(ext.toLowerCase())) {
      return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-music" viewBox="0 0 16 16">
                <path d="M10.304 3.13a1 1 0 0 1 1.196.98v1.8l-2.5.5v5.09c0 .495-.301.883-.662 1.123C7.974 12.866 7.499 13 7 13s-.974-.134-1.338-.377C5.302 12.383 5 11.995 5 11.5s.301-.883.662-1.123C6.026 10.134 6.501 10 7 10c.356 0 .7.068 1 .196V4.41a1 1 0 0 1 .804-.98z"/>
                <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1"/>
              </svg>`;
    } else if (vidsExtArray.includes(ext.toLowerCase())) {
      return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-play" viewBox="0 0 16 16">
                <path d="M6 10.117V5.883a.5.5 0 0 1 .757-.429l3.528 2.117a.5.5 0 0 1 0 .858l-3.528 2.117a.5.5 0 0 1-.757-.43z"/>
                <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1"/>
              </svg>`;
    } else if (docsExtArray.includes(ext.toLowerCase())) {
      return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-post" viewBox="0 0 16 16">
                <path d="M4 3.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z"/>
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/>
             </svg>`;
    } else {
      return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question" viewBox="0 0 16 16">
                <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94"/>
             </svg>`;
    }
  };

  const createFileItem = (file, id) => {
    const { name, size } = file;
    const ext = name.split(".").pop();

    return `<div id="file-item-${id}" class="d-flex w-100 gap-10 justify-content-between">
							<span class="badge mt-3 mb-3 bg-dark" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center">
							   ${showIconItem(ext)}	
							</span>
							<div class="w-100 p-2 ps-4">
								<div class="d-flex w-100 justify-content-between">
									<span><strong class="mb-1">${name.replace(ext, "")}</strong><strong>${ext}</strong></span>
									<button type="button" style="border:0" class="btn btn-sm ml-2 abort-upload">&times;</button>
								</div>
								<div style="gap:15px">
									<p class="mb-0">
										<span id="file-size">0 MB / ${size}</span>
										•
										<span id="file-status">Importation...</span>
									</p>
									<div class="progress mt-2" style="width: 100%;height: 5px">
										<div class="progress-bar" role="progressbar" style="width: 0%"></div>
									</div>
								</div>
							</div>
						</div>`;
  };

  const handleFiles = ([...files]) => {
    if (files.length === 0) return;

    files.forEach((file, index) => {
      const uniqId = Date.now() + index;
      const fileElement = createFileItem(file, uniqId);
      fileList.insertAdjacentHTML("afterbegin", fileElement);
      const currentFile = document.querySelector(`#file-item-${uniqId}`);

      const xhr = uploadFiles(file, uniqId);

      xhr.addEventListener("readystatechange", () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
          currentFile.querySelector("#file-status").innerText = "Terminé";
          currentFile.querySelector("#file-status").style.color = "green";
        }
      });
    });
  };

  fileBrowseBtn.addEventListener("click", function(e) {
    e.preventDefault();
    fileInput.click();
  });

  dropZone.addEventListener("dragover", function(e) {
    e.preventDefault();
    dropZone.classList.add("bg-light");
    dropZone.classList.add("active");
    dropZone.querySelector(".instruction").innerHTML =
      "Déposez pour importer ou";
  });

  dropZone.addEventListener("dragleave", function() {
    dropZone.classList.remove("bg-light");
    dropZone.classList.remove("active");
    dropZone.querySelector(".instruction").innerHTML =
      "Glissez les fichiers ici ou";
  });

  dropZone.addEventListener("drop", function(e) {
    e.preventDefault();
    dropZone.classList.remove("bg-light");
    dropZone.classList.remove("active");
    dropZone.querySelector(".instruction").innerHTML =
      "Glissez les fichiers ici ou";
    const files = e.dataTransfer.files;
    fileInput.files = files;
    handleFiles(files);
  });

  fileInput.addEventListener("change", function() {
    handleFiles(fileInput.files);
  });

  function uploadFiles(file, id) {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();
    formData.append("file", file);

    xhr.upload.addEventListener("progress", function(e) {
      const fileProgress = document.querySelector(
        `#file-item-${id} .progress-bar`
      );
      const fileSize = document.querySelector(`#file-item-${id} #file-size`);
      const progress = Math.round((e.loaded / e.total) * 100);

      const formattedFileSize =
        file.size >= 1024 * 1024
          ? `${(e.loaded / (1024 * 1024)).toFixed(2)} MB / ${(
              e.total /
              (1024 * 1024)
            ).toFixed(2)} MB`
          : `${(e.loaded / 1024).toFixed(2)} KB / ${(e.loaded / 1024).toFixed(
              2
            )} KB`;

      fileProgress.style.width = `${progress}%`;
      fileSize.innerHTML = formattedFileSize;
    });

    xhr.open("POST", form.action, true);

    xhr.send(formData);

    return xhr;
  }

});
