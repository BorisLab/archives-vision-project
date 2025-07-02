document.addEventListener("DOMContentLoaded", function() {
  //Dossiers
  const editFolder = document.getElementById("editFolder");
  const delFolder = document.getElementById("deleteFolder");

  const editFolderKeywordsInput = document.getElementById(
    "editFolderKeywordsInput"
  );
  const editFolderKeywordsContainer = document.getElementById(
    "editFolderKeywordsContainer"
  );

  function setFolderKeywordsFromString(keywordsString) {
    // Split the keywords string into an array of individual keywords
    const keywordsArray = keywordsString
      .split(",")
      .map((keyword) => keyword.trim());

    // Create a div for each keyword and append it to the keywords container
    keywordsArray.forEach((keyword) => {
      if (keyword) {
        const keywordElement = document.createElement("div");
        keywordElement.className = "keyword";
        keywordElement.textContent = keyword;

        editFolderKeywordsContainer.insertBefore(
          keywordElement,
          editFolderKeywordsInput
        );
      }
    });
  }

  if (editFolder) {
    editFolder.addEventListener("show.bs.modal", function(event) {
      let folderButton = event.relatedTarget;
      let folderId = folderButton.getAttribute("data-id");
      let folderName = folderButton.getAttribute("data-name");
      let folderFormat = folderButton.getAttribute("data-format");
      let folderStatut = folderButton.getAttribute("data-statut");
      let folderDep = folderButton.getAttribute("data-dep");
      let folderKeywords = folderButton.getAttribute("data-keywords");
      let modalBodyFolderInput = document.getElementById("editFolderName");
      let modalBodyFolderId = document.getElementById("editFolderId");
      let modalBodyFolderFormat = document.getElementById("editFolderFormat");
      let modalBodyFolderStatut = document.getElementById("editFolderStatut");
      let modalBodyFolderDep = document.getElementById(
        "edit-searchable-select-input"
      );
      let folderKYW = document.getElementById("folder_tags");
      modalBodyFolderInput.value = folderName;
      modalBodyFolderId.value = folderId;
      modalBodyFolderFormat.value = folderFormat;
      modalBodyFolderStatut.value = folderStatut;
      modalBodyFolderDep.value = folderDep;
      folderKYW.value = folderKeywords;

      setFolderKeywordsFromString(folderKeywords);

      var editFolderForm = document.getElementById("editFolderForm");
      editFolderForm.action = editFolderForm.action.replace(
        /\/\d+$/,
        "/" + folderId
      );
    });

    editFolder.addEventListener("hide.bs.modal", function(e) {
      kywds = editFolderKeywordsContainer.querySelectorAll(".keyword");
      for (let i = 0; i < kywds.length; i++) {
        editFolderKeywordsContainer.removeChild(kywds[i]);
      }
      editFolderKeywordsInput.value = "";
    });
  }

  if (delFolder) {
    delFolder.addEventListener("show.bs.modal", function(event) {
      let folderButton = event.relatedTarget;
      let folderId = folderButton.getAttribute("data-id");
      let folderName = folderButton.getAttribute("data-name");
      let modalBodyDelFolder = document.getElementById("folderToDelName");
      let modalBodyFolderId = document.getElementById("delFolderId");
      modalBodyDelFolder.innerHTML = folderName;
      modalBodyFolderId.value = folderId;

      let delFolderForm = document.getElementById("delFolderForm");
      delFolderForm.action = delFolderForm.action.replace(
        /\/\d+$/,
        "/" + folderId
      );
    });
  }

  //Fichiers
  const editFile = document.getElementById("editFile");
  const delFile = document.getElementById("deleteFile");

  const editFileKeywordsInput = document.getElementById(
    "editFileKeywordsInput"
  );
  const editFileKeywordsContainer = document.getElementById(
    "editFileKeywordsContainer"
  );

  function setFileKeywordsFromString(keywordsString) {
    // Split the keywords string into an array of individual keywords
    const keywordsArray = keywordsString
      .split(",")
      .map((keyword) => keyword.trim());

    // Create a div for each keyword and append it to the keywords container
    keywordsArray.forEach((keyword) => {
      if (keyword) {
        const keywordElement = document.createElement("div");
        keywordElement.className = "keyword";
        keywordElement.textContent = keyword;

        editFileKeywordsContainer.insertBefore(
          keywordElement,
          editFileKeywordsInput
        );
      }
    });
  }

  if (editFile) {
    editFile.addEventListener("show.bs.modal", function(event) {
      let fileButton = event.relatedTarget;
      let fileId = fileButton.getAttribute("data-id");
      let fileName = fileButton.getAttribute("data-name");
      let fileStatut = fileButton.getAttribute("data-statut");
      let fileKeywords = fileButton.getAttribute("data-keywords");
      let modalBodyFileInput = document.getElementById("editFileName");
      let modalBodyFileId = document.getElementById("editFileId");
      let modalBodyFileStatut = document.getElementById("editFileStatut");
      let fileKYW = document.getElementById("edit-file_tags");
      modalBodyFileInput.value = fileName;
      modalBodyFileId.value = fileId;
      modalBodyFileStatut.value = fileStatut;
      fileKYW.value = fileKeywords;

      setFileKeywordsFromString(fileKeywords);

      let editFileForm = document.getElementById("editFileForm");
      editFileForm.action = editFileForm.action.replace(/\/\d+$/, "/" + fileId);
    });

    editFile.addEventListener("hide.bs.modal", function(e) {
      kywds = editFileKeywordsContainer.querySelectorAll(".keyword");
      for (let i = 0; i < kywds.length; i++) {
        editFileKeywordsContainer.removeChild(kywds[i]);
      }
      editFileKeywordsInput.value = "";
    });
  }

  if (delFile) {
    delFile.addEventListener("show.bs.modal", function(event) {
      let fileButton = event.relatedTarget;
      let fileId = fileButton.getAttribute("data-id");
      let modalBodyFileId = document.getElementById("delFileId");
      modalBodyFileId.value = fileId;

      let delFileForm = document.getElementById("delFileForm");
      delFileForm.action = delFileForm.action.replace(/\/\d+$/, "/" + fileId);
    });
  }
});
