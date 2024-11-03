document.addEventListener("DOMContentLoaded", function() {
  const editFileKeywordsInput = document.getElementById("editFileKeywordsInput");
  const editFileKeywordsContainer = document.getElementById(
    "editFileKeywordsContainer"
  );
  const editFileTagsField = document.querySelector(".edit-file-tags-input");
  const editFileForm = document.querySelector("#editFileForm");

  function getFileCurrentKeywords() {
    return Array.from(
      editFileKeywordsContainer.querySelectorAll(".keyword")
    ).map((keyword) =>
      keyword.textContent
        .replace("×", "")
        .trim()
        .toLowerCase()
    );
  }

  function updateFileTagsField() {
    const tags = getFileCurrentKeywords();
    editFileTagsField.value = tags.join(",");
  }

  editFileKeywordsInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter" || event.key === "," || event.key === "." || event.key === ";" || event.key === ":" || event.key === "!" || event.key === "?") {
      event.preventDefault();
      const keyword = editFileKeywordsInput.value.trim().replace(/,$/, "");

      if (keyword) {
        addKeyword(keyword);
        editFileKeywordsInput.value = "";
      }
    }

    if (event.key === "Backspace" && !editFileKeywordsInput.value) {
      const keywords = editFileKeywordsContainer.querySelectorAll(".keyword");
      if (keywords.length > 0) {
        const lastKeyword = keywords[keywords.length - 1];
        editFileKeywordsContainer.removeChild(lastKeyword);
        updateFileTagsField();
      }
    }
  });

  editFileForm.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
    }
  });

  editFileKeywordsContainer.addEventListener("click", function(event) {
    if (event.target.classList.contains("remove-keyword")) {
      const keyword = event.target.parentElement;
      editFileKeywordsContainer.removeChild(keyword);
    }
  });

  function addKeyword(keyword) {
    const currentKeywords = getFileCurrentKeywords();

    if (currentKeywords.includes(keyword.toLowerCase())) {
      return;
    }

    const keywordElement = document.createElement("div");
    keywordElement.className = "keyword";
    keywordElement.textContent = keyword;

    const removeIcon = document.createElement("span");
    removeIcon.className = "remove-keyword";
    removeIcon.innerHTML = "&times;";
    keywordElement.appendChild(removeIcon);
    removeIcon.addEventListener("click", () => {
      editFileKeywordsContainer.removeChild(keywordElement);
      updateFileTagsField();
    });

    editFileKeywordsContainer.insertBefore(keywordElement, editFileKeywordsInput);
    updateFileTagsField();
  }
});
