document.addEventListener("DOMContentLoaded", function() {
  const addFileKeywordsInput = document.getElementById("addFileKeywordsInput");
  const addFileKeywordsContainer = document.getElementById(
    "addFileKeywordsContainer"
  );
  const addFileTagsField = document.querySelector(".add-file-tags-input");
  const addFileForm = document.querySelector("#add_phys_file");

  function getFileCurrentKeywords() {
    return Array.from(
      addFileKeywordsContainer.querySelectorAll(".keyword")
    ).map((keyword) =>
      keyword.textContent
        .replace("×", "")
        .trim()
        .toLowerCase()
    );
  }

  function updateFileTagsField() {
    const tags = getFileCurrentKeywords();
    addFileTagsField.value = tags.join(",");
  }

  addFileKeywordsInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter" || event.key === "," || event.key === "." || event.key === ";" || event.key === ":" || event.key === "!" || event.key === "?") {
      event.preventDefault();
      const keyword = addFileKeywordsInput.value.trim().replace(/,$/, "");

      if (keyword) {
        addKeyword(keyword);
        addFileKeywordsInput.value = "";
      }
    }

    if (event.key === "Backspace" && !addFileKeywordsInput.value) {
      const keywords = addFileKeywordsContainer.querySelectorAll(".keyword");
      if (keywords.length > 0) {
        const lastKeyword = keywords[keywords.length - 1];
        addFileKeywordsContainer.removeChild(lastKeyword);
        updateFileTagsField();
      }
    }
  });

  addFileForm.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
    }
  });

  addFileKeywordsContainer.addEventListener("click", function(event) {
    if (event.target.classList.contains("remove-keyword")) {
      const keyword = event.target.parentElement;
      addFileKeywordsContainer.removeChild(keyword);
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
      addFileKeywordsContainer.removeChild(keywordElement);
      updateFileTagsField();
    });

    addFileKeywordsContainer.insertBefore(keywordElement, addFileKeywordsInput);
    updateFileTagsField();
  }
});
