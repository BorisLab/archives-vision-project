document.addEventListener("DOMContentLoaded", function() {
  const addFolderKeywordsInput = document.getElementById(
    "addFolderKeywordsInput"
  );
  const addFolderKeywordsContainer = document.getElementById(
    "addFolderKeywordsContainer"
  );
  const addFolderTagsField = document.querySelector(".dossier-tags-input");
  const createFolderForm = document.querySelector("#dossier_form");


  function getFolderCurrentKeywords() {
    return Array.from(
      addFolderKeywordsContainer.querySelectorAll(".keyword")
    ).map((keyword) =>
      keyword.textContent
        .replace("×", "")
        .trim()
        .toLowerCase()
    );
  }

  function updateFolderTagsField() {
    const tags = getFolderCurrentKeywords();
    addFolderTagsField.value = tags.join(",");
  }

  addFolderKeywordsInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter" || event.key === "," || event.key === "." || event.key === ";" || event.key === ":" || event.key === "!" || event.key === "?") {
      event.preventDefault();
      const keyword = addFolderKeywordsInput.value.trim().replace(/,$/, "");

      if (keyword) {
        addKeyword(keyword);
        addFolderKeywordsInput.value = "";
      }
    }

    if (event.key === "Backspace" && !addFolderKeywordsInput.value) {
      const keywords = addFolderKeywordsContainer.querySelectorAll(".keyword");
      if (keywords.length > 0) {
        const lastKeyword = keywords[keywords.length - 1];
        addFolderKeywordsContainer.removeChild(lastKeyword);
        updateFolderTagsField();
      }
    }
  });

  createFolderForm.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
    }
  });

  addFolderKeywordsContainer.addEventListener("click", function(event) {
    if (event.target.classList.contains("remove-keyword")) {
      const keyword = event.target.parentElement;
      addFolderKeywordsContainer.removeChild(keyword);
    }
  });

  function addKeyword(keyword) {
    const currentKeywords = getFolderCurrentKeywords();

    if (currentKeywords.includes(keyword.toLowerCase())) {
      return;
    }

    const keywordElement = document.createElement("div");
    keywordElement.className = "keyword text-break";
    keywordElement.textContent = keyword;

    addFolderKeywordsContainer.insertBefore(
      keywordElement,
      addFolderKeywordsInput
    );
    updateFolderTagsField();
  }

});



