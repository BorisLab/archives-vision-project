document.addEventListener("DOMContentLoaded", function() {
  const editFolderKeywordsInput = document.getElementById(
    "editFolderKeywordsInput"
  );
  const editFolderKeywordsContainer = document.getElementById(
    "editFolderKeywordsContainer"
  );
  const editFolderTagsField = document.querySelector(".folder-tags-input");
  const editFolderForm = document.querySelector("#editFolderForm");

  function getFolderCurrentKeywords() {
    return Array.from(
      editFolderKeywordsContainer.querySelectorAll(".keyword")
    ).map((keyword) =>
      keyword.textContent
        .replace("×", "")
        .trim()
        .toLowerCase()
    );
  }

  function updateFolderTagsField() {
    const tags = getFolderCurrentKeywords();
    editFolderTagsField.value = tags.join(",");
  }

  editFolderKeywordsInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter" || event.key === "," || event.key === "." || event.key === ";" || event.key === ":" || event.key === "!" || event.key === "?") {
      event.preventDefault();
      const keyword = editFolderKeywordsInput.value.trim().replace(/,$/, "");

      if (keyword) {
        addKeyword(keyword);
        editFolderKeywordsInput.value = "";
      }
    }

    if (event.key === "Backspace" && !editFolderKeywordsInput.value) {
      const keywords = editFolderKeywordsContainer.querySelectorAll(".keyword");
      if (keywords.length > 0) {
        const lastKeyword = keywords[keywords.length - 1];
        editFolderKeywordsContainer.removeChild(lastKeyword);
        updateFolderTagsField();
      }
    }
  });

  editFolderForm.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
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

    editFolderKeywordsContainer.insertBefore(
      keywordElement,
      editFolderKeywordsInput
    );
    updateFolderTagsField();
  }
});
